<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallProductPrices extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_prices', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('price')->nullable();
            $table->integer('product_id')->unsigned();
            $table->integer('variant_id')->unsigned()->nullable();
            $table->integer('currency_id')->unsigned();
            $table->timestamps();

            $table->unique(['product_id', 'currency_id', 'variant_id'], 'unique_price');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_product_prices');
    }
}
