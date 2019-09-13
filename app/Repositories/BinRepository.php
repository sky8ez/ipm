<?php

namespace App\Repositories;


class BinRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */
      private $binWidth ;
      private $binHeight ;

      private $freeNodeIndex = 0;

     private  $usedRectangles = [];
     private  $freeRectangles = [];


     public function begin($width, $height)
    {
        $this->binWidth = 0;
        $this->binHeight = 0;
        $this->init($width, $height);
    }


     public function init($width, $height)
     {
       $this->binWidth = $width;
       $this->binHeight = $height;

       // Clear any memory of previously packed rectangles.
       $usedRectangles = array();

       // We start with a single big free rectangle that spans the whole bin.
       $n = new Rect();
       $n->x = 0;
       $n->y = 0;
       $n->width = $width;
       $n->height = $height;

       $this->freeRectangles = array();
       array_push( $this->freeRectangles,$n);

     }//function

     public function insert1($rects, $dst, $merge, $rectChoice, $splitMethod) {


     }

     public function Fits() {

     }

     public function FitsPerfectly() {

     }

     public function insert($width, $height, $merge, $rectChoice, $splitMethod) {
       // Find where to put the new rectangle.
       $this->freeNodeIndex = 0;
       $newRect = $this->FindPositionForNewNode($width, $height, $rectChoice);

       // Abort if we didn't have enough space in the bin.
       if ($newRect->height == 0) {
         return $newRect;
       }

       // Remove the space that was just consumed by the new rectangle.
       $this->SplitFreeRectByHeuristic($this->freeRectangles[$this->freeNodeIndex],$newRect,$splitMethod);
       array_splice($this->freeRectangles,$this->freeNodeIndex,1);

       // Perform a Rectangle Merge step if desired.
       if ($merge) {
         $this->MergeFreeList();
       }

       // Remember the new used rectangle.
       array_push($this->usedRectangles,$newRect);

       // Check that we're really producing correct packings here.
      //  assert(disjointRects.Add(newRect) == true); ?????

       return $newRect;

     }

     /// Computes the ratio of used surface area to the total bin area.
     public function Occupancy()  {
       ///\todo The occupancy rate could be cached/tracked incrementally instead
       ///      of looping through the list of packed rectangles here.
       $usedSurfaceArea = 0;
       for($i = 0; $i < count($this->usedRectangles);$i++) {
         $usedSurfaceArea = $usedSurfaceArea + $this->usedRectangles[$i]->width * $this->usedRectangles[$i]->height;
       }
       return  $usedSurfaceArea / ($this->binWidth * $this->binHeight);
     }

     /// Returns the heuristic score value for placing a rectangle of size width*height into freeRect. Does not try to rotate.
     public function ScoreByHeuristic($width, $height, $freeRect, $rectChoice) {
       switch ($rectChoice) {
         case 'RectBestAreaFit':
           return $this->ScoreBestAreaFit($width, $height, $freeRect);
           break;
         case 'RectBestShortSideFit':
          return $this->ScoreBestShortSideFit($width, $height, $freeRect);
           break;
         case 'RectBestLongSideFit':
           return $this->ScoreBestLongSideFit($width, $height, $freeRect);
           break;
         case 'RectWorstAreaFit':
           return $this->ScoreWorstAreaFit($width, $height, $freeRect);
           break;
         case 'RectWorstShortSideFit':
           return $this->ScoreWorstShortSideFit($width, $height, $freeRect);
           break;
         case 'RectWorstLongSideFit':
           return $this->ScoreWorstLongSideFit($width, $height, $freeRect);
           break;
         default:
          // assert(false); ????
          // return  std::numeric_limits<int>::max();

           break;
       }
     }

     public function ScoreBestAreaFit($width, $height, $freeRect) {
       return $freeRect->width * $freeRect->height - $width * $height;
     }

     public function ScoreBestShortSideFit($width, $height, $freeRect) {
       $leftoverHoriz = abs($freeRect->width - $width);
       $leftoverVert = abs($freeRect->height - $height);
       $leftover = min($leftoverHoriz,$leftoverVert);
       return $leftover;
     }

     public function ScoreBestLongSideFit($width, $height, $freeRect) {
       $leftoverHoriz = abs($freeRect->width - $width);
       $leftoverVert = abs($freeRect->height - $height);
       $leftover = max($leftoverHoriz,$leftoverVert);
       return $leftover;
     }

     public function ScoreWorstAreaFit($width, $height, $freeRect) {
       return $this->ScoreBestAreaFit($width, $height, $freeRect) * -1;
     }

     public function ScoreWorstShortSideFit($width, $height, $freeRect) {
       return $this->ScoreBestShortSideFit($width, $height, $freeRect) * -1;
     }

     public function ScoreWorstLongSideFit($width, $height, $freeRect) {
       return $this->ScoreBestLongSideFit($width, $height, $freeRect) * -1;
     }

     public function FindPositionForNewNode($width, $height, $rectChoice) {
       $bestNode = new Rect();
      //  memset(&bestNode, 0, sizeof(Rect));

      $bestScore = 2147483647;
      //  std::numeric_limits<int>::max();

      /// Try each free rectangle to find the best one for placement.
      for($i=0;$i< count($this->freeRectangles);$i++) {
        // echo count($this->freeRectangles)."<br>";
        // If this is a perfect fit upright, choose it immediately.
        if($width == $this->freeRectangles[$i]->width && $height == $this->freeRectangles[$i]->height) {

          $bestNode->x = $this->freeRectangles[$i]->x;
          $bestNode->y = $this->freeRectangles[$i]->y;
          $bestNode->width = $width;
          $bestNode->height  = $height;
          $bestScore = -2147483647;
          // $bestScore = std::numeric_limits<int>::min();
          $this->freeNodeIndex = $i;
          // assert(disjointRects.Disjoint(bestNode));
          break;

          // If this is a perfect fit sideways, choose it.
        } elseif ($height == $this->freeRectangles[$i]->width && $width == $this->freeRectangles[$i]->height) {

          $bestNode->x = $this->freeRectangles[$i]->x;
          $bestNode->y = $this->freeRectangles[$i]->y;
          $bestNode->width = $height;
          $bestNode->height  = $width;
          $bestScore = -2147483647;
          $this->freeNodeIndex = $i;
          // Does the rectangle fit upright?
          break;
        } elseif ($width <= $this->freeRectangles[$i]->width && $height <= $this->freeRectangles[$i]->height) {
            $score = $this->ScoreByHeuristic($width,$height, $this->freeRectangles[$i],$rectChoice);

            if ($score < $bestScore) {
              $bestNode->x = $this->freeRectangles[$i]->x;
              $bestNode->y = $this->freeRectangles[$i]->y;
              $bestNode->width = $width;
              $bestNode->height  = $height;
              $bestScore = $score;
              $this->freeNodeIndex = $i;
              // assert(disjointRects.Disjoint(bestNode));

            }
          // Does the rectangle fit sideways?
        } elseif ($height <= $this->freeRectangles[$i]->width && $width <= $this->freeRectangles[$i]->height) {
            $score = $this->ScoreByHeuristic($height,$width,$this->freeRectangles[$i],$rectChoice);

            if ($score < $bestScore) {
              $bestNode->x = $this->freeRectangles[$i]->x;
              $bestNode->y = $this->freeRectangles[$i]->y;
              $bestNode->width = $height;
              $bestNode->height  = $width;
              $bestScore = $score;
              $this->freeNodeIndex = $i;
              // assert(disjointRects.Disjoint(bestNode));
            }
        }

      }



      return $bestNode;
     }

     public function SplitFreeRectByHeuristic($freeRect, $placedRect, $method) {
       // Compute the lengths of the leftover area.
       $w = $freeRect->width - $placedRect->width;
       $h = $freeRect->height - $placedRect->height;

       // Placing placedRect into freeRect results in an L-shaped free area, which must be split into
    	// two disjoint rectangles. This can be achieved with by splitting the L-shape using a single line.
    	// We have two choices: horizontal or vertical.

    	// Use the given heuristic to decide which choice to make.

      $splitHorizontal = false;
      switch ($method) {
        case 'SplitShorterLeftoverAxis':
          // Split along the shorter leftover axis.
          $splitHorizontal = ($w <= $h);
          break;
        case 'SplitLongerLeftoverAxis':
          // Split along the longer leftover axis.
      		$splitHorizontal = ($w > $h);
          break;
        case 'SplitMinimizeArea':
          // Maximize the larger area == minimize the smaller area.
          // Tries to make the single bigger rectangle.
          $splitHorizontal = ($placedRect->width * $h > $w * $placedRect->height);
          break;
        case 'SplitMaximizeArea':
          // Maximize the smaller area == minimize the larger area.
          // Tries to make the rectangles more even-sized.
          $splitHorizontal = ($placedRect->width * $h <= $w * $placedRect->height);
          break;
        case 'SplitShorterAxis':
        // Split along the shorter total axis.
          $splitHorizontal = ($freeRect->width <= $freeRect->height);
          break;
        case 'SplitLongerAxis':
          // Split along the longer total axis.
          $splitHorizontal = ($freeRect->width > $freeRect->height);
          break;
        default:
          $splitHorizontal = true;
          // assert(false);
          break;
      }

      // Perform the actual split.
	       $this->SplitFreeRectAlongAxis($freeRect, $placedRect, $splitHorizontal);

     }


    public function SplitFreeRectAlongAxis($freeRect, $placedRect, $splitHorizontal) {
        // Form the two new rectangles.
        $bottom = new Rect();
        $bottom->x = $freeRect->x;
        $bottom->y = $freeRect->y + $placedRect->height;
        $bottom->height = $freeRect->height - $placedRect->height;

        $right = new Rect();
        $right->x = $freeRect->x + $placedRect->width;
        $right->y = $freeRect->y;
        $right->width = $freeRect->width - $placedRect->width;

        if ($splitHorizontal) {
          $bottom->width = $freeRect->width;
          $right->height = $placedRect->height;
        } else { // Split vertically
          $bottom->width = $placedRect->width;
          $right->height = $freeRect->height;
        }

        // Add the new rectangles into the free rectangle pool if they weren't degenerate.
        if ($bottom->width > 0 && $bottom->height > 0) {
          array_push($this->freeRectangles,$bottom);
        }
        if ($right->width > 0 && $right->height > 0) {
          array_push($this->freeRectangles,$right);
        }

      //   assert(disjointRects.Disjoint(bottom));
      //  assert(disjointRects.Disjoint(right));

    }


  public function MergeFreeList()
 {
   // Do a Theta(n^2) loop to see if any pair of free rectangles could me merged into one.
	// Note that we miss any opportunities to merge three rectangles into one. (should call this function again to detect that)
   for($i=0;$i<count($this->freeRectangles);$i++) {
     for($j=$i+1;$j<count($this->freeRectangles);$j++) {
        if ($this->freeRectangles[$i]->width == $this->freeRectangles[$j]->width && $this->freeRectangles[$i]->x == $this->freeRectangles[$j]->x) {
            if ($this->freeRectangles[$i]->y == $this->freeRectangles[$j]->y + $this->freeRectangles[$j]->height) {
              $this->freeRectangles[$i]->y = $this->freeRectangles[$i]->y - $this->freeRectangles[$j]->height;
              $this->freeRectangles[$i]->height = $this->freeRectangles[$i]->height + $this->freeRectangles[$j]->height;
              array_splice($this->freeRectangles,$j,1);
              $j = $j - 1;
            } else if ($this->freeRectangles[$i]->y + $this->freeRectangles[$i]->height == $this->freeRectangles[$j]->y) {
              $this->freeRectangles[$i]->height = $this->freeRectangles[$i]->y + $this->freeRectangles[$j]->height;
              array_splice($this->freeRectangles,$j,1);
              $j = $j - 1;
            }
        } else if ($this->freeRectangles[$i]->height == $this->freeRectangles[$j]->height && $this->freeRectangles[$i]->y == $this->freeRectangles[$j]->y) {
            if ($this->freeRectangles[$i]->x == $this->freeRectangles[$j]->x + $this->freeRectangles[$j]->width) {
              $this->freeRectangles[$i]->x = $this->freeRectangles[$i]->x - $this->freeRectangles[$i]->width;
              $this->freeRectangles[$i]->width = $this->freeRectangles[$i]->width + $this->freeRectangles[$j]->width;
              array_splice($this->freeRectangles,$j,1);
              $j = $j - 1;
            } else if ($this->freeRectangles[$i]->x + $this->freeRectangles[$i]->width == $this->freeRectangles[$j]->x) {
              $this->freeRectangles[$i]->width = $this->freeRectangles[$i]->width + $this->freeRectangles[$j]->width;
              array_splice($this->freeRectangles,$j,1);
              $j = $j - 1;
            }
        }



     }
   }
 }


}


class RectSize {
    public $width;
    public $height;
}


class Rect {
    public $x;
    public $y;
    public $width;
    public $height;
}
