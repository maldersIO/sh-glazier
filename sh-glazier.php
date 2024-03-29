<?php add_filter( 'woocommerce_package_rates', 'fs_add_extra_cost_based_on_shipping_class', 10, 2);

if( ! function_exists('fs_add_extra_cost_based_on_shipping_class') ) {
		
function fs_add_extra_cost_based_on_shipping_class( $shipping_rates, $package ){
	
	//var_dump($shipping_rates);

  if (!empty($shipping_rates)) { //Checking if there are packages

	 //var_dump($package['rates'][0]);
		
	// Define the Shipping Classes to Count
	$perishable_shipping_class ='perishable'; 
	$shelf_stable_shipping_class = 'shelf-stable';
	
	// Define Special Shelf Stable Categories 
	$shelf_stable_smoked_meat = 'adirondack-smoked-meats';
	$shelf_stable_glazier_hot_dogs = 'glazier-brand-hot-dogs';
	$shelf_stable_pickled_products = 'glaziers-pickled-products';
	
	
		// Get cart items
    $cart_items = $package['contents'];
	
	//$first_shipping_rate=$package['rates']['flexible_shipping_ups:7:12']->get_meta_data()["packed_packages"];
	$firstElement = reset($shipping_rates);
	$first_shipping_rate=$firstElement->get_meta_data()["packed_packages"];
	//var_dump($firstElement->get_meta_data()["packed_packages"]);
	//var_dump($first_shipping_rate); // Checking
	$packagecount = substr_count($first_shipping_rate,'"package"'); //Here u have the value
	//var_dump($packagecount);
	
	$packagcount_multiplier = strval($packagecount); 
	
	//var_dump($packagecount);  //Just checking
	
	//var_dump($packagcount_multiplier); 
	
	
	//var_dump(count($package['rates']['flexible_shipping_ups:7:12']->get_meta_data()["packed_packages"]));
	//var_dump($package);
        // Check all cart items
        foreach ($cart_items as $cart_item) {
			
			// Get the Category of each item in the cart
			$terms = get_the_terms( $product_id, 'product_cat' );
				foreach ($terms as $term) {
   				$product_cat = $term->name;
			}
			/*
			// Get the Category Quantity
			if ($cart_item['data']->$terms == $shelf_stable_pickled_products){
				$shelf_stable_pickled_count += $cart_item['quantity'];
			}
			else if($cart_item['data']->$terms == $shelf_stable_smoked_meat){
				$shelf_stable_smoked_count += $cart_item['quantity'];
			} */
			
            // Get shipping class quantity
          
			if( $cart_item['data']->get_shipping_class() == $perishable_shipping_class ) {
            $perishable_count += $cart_item['quantity'];
        	}
			
			else if( $cart_item['data']->get_shipping_class() == $shelf_stable_shipping_class ) {
            $shelf_stable_count += $cart_item['quantity'];
        	}
			
			
		}
	//Handling Fees
	if ($perishable_count >= 1){
	$perishable_fee = 8.00;
	}
	if ($shelf_stable_count >= 1){
	$shelf_stable_smoked_fee = 6.00;
	}
	if ($shelf_stable_count >= 1){
	$shelf_stable_pickled_fee = 7.00;
	}
	
	$perishable_fee_variable = $packagcount_multiplier * $perishable_fee; 
	//var_dump($perishable_fee_variable);
	
	
	$shelf_stable_cat_fee_smoked = $packagcount_multiplier * $shelf_stable_smoked_fee; 
	//var_dump($shelf_stable_cat_fee_smoked); 
	
	$shelf_stable_cat_fee_pickled = $packagcount_multiplier * $shelf_stable_pickled_fee;
	//var_dump($shelf_stable_cat_fee_pickled); 
	
	
	//var_dump($perishable_fee_variable);

	if($shelf_stable_cat_fee_pickled >= $shelf_stable_cat_fee_smoked){
		$shelfstable_fee_variable = $shelf_stable_cat_fee_pickled;
	}
	if($shelf_stable_cat_fee_smoked >= $shelf_stable_cat_fee_pickled){
		$shelfstable_fee_variable = $shelf_stable_cat_fee_smoked;
	}
	

	//$shelfstable_fee_variable = $shelf_stable_cat_fee_pickled + $shelf_stable_cat_fee_smoked;
	//var_dump($shelfstable_fee_variable);
	if($perishable_fee_variable >= $shelfstable_fee_variable){
	//Custom shipping and handling function here
	$handling_fee_based_on_shipping_class = array(
		array(
			'shipping_classes'    =>    array( 'perishable'),
			'adjustment'        => $perishable_fee_variable,
		),
		
		/*
		array(
			'shipping_classes'  =>    array( 'shelf-stable' ),
			'adjustment'        => $shelfstable_fee_variable,
		),*/

	);
	}
	
	if($shelfstable_fee_variable >= $perishable_fee_variable){
	//Custom shipping and handling function here
	$handling_fee_based_on_shipping_class = array(
		/*array(
			'shipping_classes'    =>    array( 'perishable'),
			'adjustment'        => $perishable_fee_variable,
		),
		*/
		
		array(
			'shipping_classes'  =>    array( 'shelf-stable' ),
			'adjustment'        => $shelfstable_fee_variable,
		),

	);
	}
	//var_dump($handling_fee_based_on_shipping_class);
	$shipping_method_ids = array( 'flexible_shipping_ups');  // Shipping method ID on which adjustment has to be applied
	$adjustment = null;
	foreach( $package['contents'] as  $line_item ) {
		$line_item_shipping_class = $line_item['data']->get_shipping_class();
			if( ! empty($line_item_shipping_class) ) {
				foreach( $handling_fee_based_on_shipping_class as $adjustment_data ) {
					if( in_array( $line_item_shipping_class, $adjustment_data['shipping_classes']) ) {
						$adjustment = ( $adjustment_data['adjustment'] > $adjustment ) ? $adjustment_data['adjustment'] : $adjustment;
					}
				}
			}
	}
	
	if( ! empty($adjustment) ) {
		foreach( $shipping_rates as $shipping_rate ) {
			$shipping_method_id = $shipping_rate->get_method_id();
				if( in_array($shipping_method_id, $shipping_method_ids) ) {
				$shipping_rate->set_cost( (float) $shipping_rate->get_cost() + $adjustment );
				}
		}
	}
	
	return $shipping_rates;
	
} else {
	 printf ("No rates found, below the \$package var_dump<br /><br />");
  
	//var_dump($package);
} //closing isset

}
	
}

