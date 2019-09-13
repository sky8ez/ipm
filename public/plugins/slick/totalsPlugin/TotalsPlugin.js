function TotalsPlugin(scrollbarWidth) {
  this._scrollbarWidth = scrollbarWidth;
}

TotalsPlugin.prototype._scrollOffset = 0;

TotalsPlugin.prototype._scrollbarWidth = 16;

TotalsPlugin.prototype._rowHeight = 0;

TotalsPlugin.prototype._$totalsViewport = null;

TotalsPlugin.prototype._$totalsRow = null;

TotalsPlugin.prototype.init = function (grid) {
  this._grid = grid;
  this._rowHeight = grid.getOptions().rowHeight;

  var viewport = grid.getCanvasNode().parentElement;
  var width = viewport.offsetWidth;
  if (viewport.scrollHeight > viewport.offsetHeight) {
    width -= this._scrollbarWidth;
  }

  this._$totalsViewport = $('<div class="slick-viewport totals-viewport">').css({top: this._rowHeight * -1, width: width});
  this._$totalsViewport.insertAfter(viewport);
  this._appendTotalsRow(grid);

  var self = this;
  grid.onColumnsResized.subscribe(function() { self._handleColumnsResized.apply(self, arguments) });
  grid.onColumnsReordered.subscribe(function() { self._handleColumnsReordered.apply(self, arguments) });
  grid.onScroll.subscribe(function() { self._handleScroll.apply(self, arguments) });
};

TotalsPlugin.prototype.destroy = function () {
  this._$totalsViewport.remove();
};

TotalsPlugin.prototype.render = function () {
  var totals = this._grid.getData().getTotals();
  var columns = this._grid.getColumns();
  var cells = this._$totalsRow.children();

  for (var i = 0, l = columns.length; i < l; i++) {
    cells[i].innerText = addCommas(totals[columns[i].id]) || '';
    // cells[i].innerText = totals[columns[i].id] || '';
  }
};

function addCommas(nStr) {
    if (nStr == null) {
    }  else {
      nStr += '';
      var x = nStr.split('.');
      var x1 = x[0];
      var x2 = x.length > 1 ? '.' + x[1] : '';
      var rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
          x1 = x1.replace(rgx, '$1' + ',' + '$2');
      }
      return x1 + x2;
      // "Rp " +
    }
  return '';
}

TotalsPlugin.prototype._appendTotalsRow = function (grid) {
  var width = grid.getCanvasNode().offsetWidth;
  var $totalsRow = $('<div class="ui-widget-content slick-row totals"></div>').css({position: 'relative', width: width});
  var totals = grid.getData().getTotals();
  var columns = grid.getColumns();
  var $cell;

  for (var i = 0, l = columns.length; i < l; i++) {
    $cell = $('<div class="slick-cell"></div>').addClass('l' + i + ' r' + i);
    $cell.text(totals[columns[i].id]);
    $totalsRow.append($cell);
  }

  this._$totalsViewport.empty().append($totalsRow);
  this._$totalsRow = $totalsRow;
};

TotalsPlugin.prototype._handleColumnsResized = function (event, update) {
  var canvas = update.grid.getCanvasNode();
  var viewport = canvas.parentElement;
  var top = (viewport.scrollWidth > viewport.offsetWidth) ? this._rowHeight + this._scrollbarWidth : this._rowHeight;
  this._$totalsRow.width(canvas.scrollWidth);
  this._$totalsViewport.css('top', top * -1 + 'px')
};

TotalsPlugin.prototype._handleColumnsReordered = function(event, update) {
  this._appendTotalsRow(update.grid);
};

TotalsPlugin.prototype._handleScroll = function(event, update) {
  if (this._scrollOffset != update.scrollLeft) {
    this._scrollOffset = update.scrollLeft;
    this._$totalsRow.css('left', this._scrollOffset * -1);
  }
};
