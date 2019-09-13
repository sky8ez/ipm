<?php

namespace App\Repositories;

use Schema;

class GenerateRepository
{
    /**
     * Get all of the tasks for a given user.
     *
     * @param  User  $user
     * @return Collection
     */

     public function generateNewPeriod($year)
     {
       Schema::connection('mysql')->create('tr_sales_order'.$year, function($table)
        {
            $table->increments('id');
            $table->string('sales_order_no',25)->unique();
            $table->datetime('sales_order_date');
            $table->integer('customer_id')->index();
            $table->integer('contact_id')->index();
            $table->integer('address_id')->index();
            $table->integer('top_id')->index();
            $table->string('description');
            $table->double('sub_total');
            $table->double('discount');
            $table->double('total');
            $table->double('tax');
            $table->double('grand_total');
            $table->boolean('delete_flag')->default(false);
            $table->timestamps();
        });

       Schema::connection('mysql')->create('tr_sales_order_detail'.$year, function($table)
       {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('sales_order_id')->index();
           $table->integer('row_id')->index();
           $table->integer('item_id')->index();
           $table->string('item_name');
           $table->integer('unit_id')->index();
           $table->double('quantity');
           $table->double('price');
           $table->double('sub_total');
           $table->double('discount');
           $table->double('discount_percentage');
           $table->double('total');
           $table->double('tax');
           $table->double('grand_total');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
       });

       Schema::connection('mysql')->create('tr_delivery'.$year, function($table)
        {
          $table->increments('id');
          $table->string('delivery_no',25)->unique();
          $table->datetime('delivery_date');
          $table->integer('customer_id')->index();
          $table->integer('contact_id')->index();
          $table->integer('address_id')->index();
          $table->integer('top_id')->index();
          $table->string('description');
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

      Schema::connection('mysql')->create('tr_delivery_detail'.$year, function($table)
       {
         $table->increments('id');
         $table->integer('sequence_no');
         $table->integer('delivery_id')->index();
         $table->integer('warehouse_id')->index();
         $table->integer('row_id')->index();
         $table->integer('item_id')->index();
         $table->integer('unit_id')->index();
         $table->string('description');
         $table->double('quantity');
         $table->boolean('delete_flag')->default(false);
         $table->timestamps();
       });

       Schema::connection('mysql')->create('tr_sales_invoice'.$year, function($table)
        {
          $table->increments('id');
          $table->string('invoice_no')->unique(); // no invoice internal
          $table->string('tax_no')->unique(); // no pajak
          $table->datetime('invoice_date'); //
          $table->integer('customer_id')->index();
          $table->integer('contact_id')->index();
          $table->integer('address_id')->index();
          $table->integer('top_id')->index();
          $table->string('description');
          $table->double('sub_total');
          $table->double('discount');
          $table->double('total');
          $table->double('tax');
          $table->double('grand_total');
          $table->integer('user_id')->index(); // user yang membuat
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

      Schema::connection('mysql')->create('tr_sales_invoice_detail'.$year, function($table)
       {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('invoice_id')->index();
           $table->integer('sales_order_id')->index();
           $table->integer('delivery_id')->index();
           $table->integer('warehouse_id')->index();
           $table->integer('item_id')->index();
           $table->integer('promotion_id')->index();
           $table->string('item_name');
           $table->double('quantity');
           $table->double('large_quantity');
           $table->integer('unit_id')->index();
           $table->double('price');
           $table->double('sub_total');
           $table->double('discount_percentage');
           $table->double('discount');
           $table->double('total');
           $table->double('tax_percentage');
           $table->double('tax');
           $table->boolean('tax_flag');
           $table->double('grand_total');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
       });

       Schema::connection('mysql')->create('tr_ledger'.$year, function($table)
        {
            $table->increments('id');
            $table->string('type'); //
            $table->integer('parent_id')->index();
            $table->integer('customer_id')->index();
            $table->integer('supplier_id')->index();
            $table->integer('account_id')->index();
            $table->double('debit');
            $table->double('credit');
            $table->string('description');
            $table->boolean('active_flag'); //hanya jika nilai 0 bisa di nonaktifkan
            $table->boolean('delete_flag')->default(false);
            $table->timestamps();
        });

      Schema::connection('mysql')->create('tr_stock_card'.$year, function($table)
       {
         $table->increments('id');
         $table->string('date_yyyy',4)->index(); //
         $table->string('date_mm',2)->index(); //
         $table->string('date_dd',2)->index(); //
         $table->string('transaction_category',2); // DO /
         $table->string('transaction_no',25); //
         $table->integer('transaction_id')->index(); //
         $table->integer('warehouse_id')->index(); //
         $table->integer('item_id')->index(); //
         $table->string('type',2)->index(); //
         $table->double('qty'); //
         $table->double('price'); //
         $table->boolean('delete_flag')->default(false);
         $table->timestamps();
       });

       Schema::connection('mysql')->create('tr_journal'.$year, function($table)
        {
          $table->increments('id');
          $table->string('type');//KELUAR, MASUK, UMUM
          $table->string('journal_no')->unique();
          $table->datetime('journal_date');
          $table->string('description');
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

      Schema::connection('mysql')->create('tr_journal_detail'.$year, function($table)
       {
         $table->increments('id');
         $table->integer('sequence_no');
         $table->integer('journal_id')->index();
         $table->integer('account_id')->index();
         $table->double('debit');
         $table->double('credit');
         $table->string('description');
         $table->boolean('delete_flag')->default(false);
         $table->timestamps();
       });

       Schema::connection('mysql')->create('tr_purchase_order'.$year, function($table)
        {
          $table->increments('id');
          $table->string('purchase_order_no',25)->unique();
          $table->string('supplier_so_no',25);
          $table->datetime('purchase_order_date');
          $table->integer('supplier_id')->index();
          $table->integer('contact_id')->index();
          $table->integer('address_id')->index();
          $table->integer('top_id')->index();
          $table->string('description');
          $table->double('sub_total');
          $table->double('discount');
          $table->double('total');
          $table->double('tax');
          $table->double('grand_total');
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

      Schema::connection('mysql')->create('tr_purchase_order_detail'.$year, function($table)
       {
         $table->increments('id');
         $table->integer('sequence_no');
         $table->integer('purchase_order_id')->index();
         $table->integer('row_id')->index();
         $table->integer('item_id')->index();
         $table->string('item_name');
         $table->integer('unit_id')->index();
         $table->double('quantity');
         $table->double('price');
         $table->double('sub_total');
         $table->double('discount');
         $table->double('discount_percentage');
         $table->double('total');
         $table->double('tax');
         $table->double('grand_total');
         $table->boolean('delete_flag')->default(false);
         $table->timestamps();
       });

       Schema::connection('mysql')->create('tr_receipt'.$year, function($table)
        {
          $table->increments('id');
          $table->string('receipt_no',25)->unique();
          $table->datetime('receipt_date');
          $table->integer('customer_id')->index();
          $table->integer('contact_id')->index();
          $table->integer('address_id')->index();
          $table->integer('top_id')->index();
          $table->string('description');
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

        Schema::connection('mysql')->create('tr_receipt_detail'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('receipt_id')->index();
           $table->integer('warehouse_id')->index();
           $table->integer('row_id')->index();
           $table->integer('item_id')->index();
           $table->integer('unit_id')->index();
           $table->string('description');
           $table->double('quantity');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

       Schema::connection('mysql')->create('tr_purchase_invoice'.$year, function($table)
        {
          $table->increments('id');
          $table->string('invoice_no')->unique(); // no invoice internal
          $table->string('supp_invoice_no'); // no invoice dr supplier
          $table->string('tax_no'); // no pajak
          $table->datetime('invoice_date'); //
          $table->integer('supplier_id')->index();
          $table->integer('contact_id')->index();
          $table->integer('address_id')->index();
          $table->integer('top_id')->index();
          $table->string('description');
          $table->double('sub_total');
          $table->double('discount');
          $table->double('total');
          $table->double('tax');
          $table->double('grand_total');
          $table->integer('user_id')->index(); // user yang membuat
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

        Schema::connection('mysql')->create('tr_purchase_invoice_detail'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('invoice_id')->index();
           $table->integer('purchase_order_id')->index();
           $table->integer('receipt_id')->index();
           $table->integer('warehouse_id')->index();
           $table->integer('item_id')->index();
           $table->string('item_name');
           $table->double('quantity');
           $table->double('large_quantity');
           $table->integer('unit_id')->index();
           $table->double('price');
           $table->double('sub_total');
           $table->double('discount_percentage');
           $table->double('discount');
           $table->double('total');
           $table->double('tax_percentage');
           $table->double('tax');
           $table->boolean('tax_flag');
           $table->double('grand_total');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

       Schema::connection('mysql')->create('tr_account_payable'.$year, function($table)
        {
          $table->increments('id');
          $table->string('payment_no')->unique();
          $table->datetime('payment_date');
          $table->integer('supplier_id')->index();
          $table->string('description');
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

        Schema::connection('mysql')->create('tr_account_payable_detail'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('ap_id')->index();
           $table->integer('ap_method_id')->index();
           $table->integer('invoice_id')->index();
           $table->double('amount'); // total piutang
           $table->double('payment_amount'); // total yang dilunasi
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

       Schema::connection('mysql')->create('tr_account_payable_method'.$year, function($table)
        {
          $table->increments('id');
          $table->integer('sequence_no');
          $table->integer('ap_id')->index();
          $table->string('method'); // cash, bank, selisih
          $table->integer('account_id')->index();
          $table->double('amount'); // total penerimaan
          $table->boolean('delete_flag')->default(false);
          $table->timestamps();
        });

        Schema::connection('mysql')->create('tr_account_receivable'.$year, function($table)
         {
           $table->increments('id');
           $table->string('payment_no')->unique();
           $table->datetime('payment_date');
           $table->integer('customer_id')->index();
           $table->string('description');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

         Schema::connection('mysql')->create('tr_account_receivable_detail'.$year, function($table)
          {
            $table->increments('id');
            $table->integer('sequence_no');
            $table->integer('ar_id')->index();
            $table->integer('ar_method_id')->index();
            $table->integer('invoice_id')->index();
            $table->double('amount'); // total piutang
            $table->double('payment_amount'); // total yang dilunasi
            $table->boolean('delete_flag')->default(false);
            $table->timestamps();
          });

        Schema::connection('mysql')->create('tr_account_receivable_method'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('ar_id')->index();
           $table->string('method'); // cash, bank, selisih
           $table->integer('account_id')->index();
           $table->double('amount'); // total penerimaan
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

         Schema::connection('mysql')->create('tr_audit'.$year, function($table)
          {
            $table->increments('id');
            $table->string('transaction_category');
            $table->integer('transaction_id')->index();
            $table->string('status'); //insert,edit,delete
            $table->string('column'); //
            $table->string('value_old'); //
            $table->string('value_new'); //
            $table->integer('modified_user_id'); //perubah
            $table->boolean('delete_flag')->default(false);
            $table->timestamps();
          });

        Schema::connection('mysql')->create('tr_audit_detail'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('audit_id')->index();
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

         Schema::connection('mysql')->create('tr_stock_adj'.$year, function($table)
          {
            $table->increments('id');
            $table->string('adjustment_no');
            $table->datetime('adjustment_date');
            $table->integer('account_id')->index();
            $table->string('description');
            $table->boolean('delete_flag')->default(false);
            $table->timestamps();
          });

        Schema::connection('mysql')->create('tr_stock_adj_detail'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('stock_adjustment_id')->index();
           $table->integer('warehouse_id')->index();
           $table->integer('item_id')->index();
           $table->double('quantity');
           $table->double('price');
           $table->double('total');
           $table->string('description');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });

         Schema::connection('mysql')->create('tr_stock_relocation'.$year, function($table)
          {
            $table->increments('id');
            $table->string('stock_relocation_no')->unique();
            $table->datetime('stock_relocation_date');
            $table->integer('warehouse_from_id')->index();
            $table->integer('warehouse_to_id')->index();
            $table->string('description');
            $table->boolean('delete_flag')->default(false);
            $table->timestamps();
          });

        Schema::connection('mysql')->create('tr_stock_relocation_detail'.$year, function($table)
         {
           $table->increments('id');
           $table->integer('sequence_no');
           $table->integer('stock_relocation_id')->index();
           $table->integer('item_id')->index();
           $table->double('quantity');
           $table->string('description');
           $table->boolean('delete_flag')->default(false);
           $table->timestamps();
         });
     }//function


     public function dropPeriod($year)
     {
        Schema::connection('mysql')->drop('tr_sales_order'.$year);

        Schema::connection('mysql')->drop('tr_sales_order_detail'.$year);

        Schema::connection('mysql')->drop('tr_delivery'.$year);

        Schema::connection('mysql')->drop('tr_delivery_detail'.$year);

        Schema::connection('mysql')->drop('tr_sales_invoice'.$year);

        Schema::connection('mysql')->drop('tr_sales_invoice_detail'.$year);

        Schema::connection('mysql')->drop('tr_ledger'.$year);

        Schema::connection('mysql')->drop('tr_stock_card'.$year);

        Schema::connection('mysql')->drop('tr_journal'.$year);

        Schema::connection('mysql')->drop('tr_journal_detail'.$year);

        Schema::connection('mysql')->drop('tr_purchase_order'.$year);

        Schema::connection('mysql')->drop('tr_purchase_order_detail'.$year);

        Schema::connection('mysql')->drop('tr_receipt'.$year);

        Schema::connection('mysql')->create('tr_receipt_detail'.$year);

        Schema::connection('mysql')->create('tr_purchase_invoice'.$year);

        Schema::connection('mysql')->create('tr_purchase_invoice_detail'.$year);

        Schema::connection('mysql')->create('tr_account_payable'.$year);

        Schema::connection('mysql')->create('tr_account_payable_detail'.$year);

        Schema::connection('mysql')->create('tr_account_payable_method'.$year);

        Schema::connection('mysql')->create('tr_account_receivable'.$year);

        Schema::connection('mysql')->create('tr_account_receivable_detail'.$year);

        Schema::connection('mysql')->create('tr_account_receivable_method'.$year);

        Schema::connection('mysql')->create('tr_audit'.$year);

        Schema::connection('mysql')->create('tr_audit_detail'.$year);

        Schema::connection('mysql')->create('tr_stock_adj'.$year);

        Schema::connection('mysql')->create('tr_stock_adj_detail'.$year);

        Schema::connection('mysql')->create('tr_stock_relocation'.$year);

        Schema::connection('mysql')->create('tr_stock_relocation_detail'.$year);
     }//function


}
