<?php

$mysqli = mysqli_connect("localhost","root","","mobimark_grocery");
$sql = "Select * from products where processed = 0";  // try with olnaaj

$result = mysqli_query($mysqli, $sql);
if(mysqli_num_rows($result) > 0)
{
	//;
	
	while($p = mysqli_fetch_array($result))
	{
		
		$image_file = "";
		$sql_img = "select * from product_images where product_id = " . $p['id'];
		//echo "\n".$sql_img;
		$result_img = mysqli_query($mysqli, $sql_img);
		if(mysqli_num_rows($result_img) == 0)
		{
			echo "Image Record Not Found For:".$p['id']."\n";
			//exit;
		}
		else
		{
			$img = mysqli_fetch_array($result_img);
			if(is_file('catalog/bulk/'.$img['image_name']))
			{
				$image_file = 'catalog/bulk/'.$img['image_name'];
			}
			
		}
		// date_available
		$sql_oc = "insert into oc_product SET model = '', quantity = 1, stock_status_id = 6, shipping = 1, weight_class_id = 1,
		status = 1,
		sku = '',
		upc = '',
		ean = '', jan = '', isbn = '', mpn = '', location = '', manufacturer_id = 0, tax_class_id = 0, date_added = now(),
		date_modified = now(), date_available = now(),
		image='$image_file',
		price = '".$p['price']."',
		weight = '".$p['grams']."'";
		//echo $sql_oc;
		
		try {
			if(!mysqli_query($mysqli, $sql_oc))
			{
				echo "\n$sql_oc\n";
				echo("Error description: " . mysqli_error($mysqli));
			}
			
		}
		catch(\Exception $e)
		{
			
			print_r($e); 
			echo "\n product_id:".$p['id'];
			exit;
		}
		$oc_pid = mysqli_insert_id($mysqli);
		
		//echo "oc_pid:$oc_pid"; exit;
		///
		try
		{
			$lid = 1;
			$tag = $meta_desc = $meta_keyword = '';
			$stmt = mysqli_prepare($mysqli, "insert into oc_product_description SET product_id = ?, language_id = ?, name = ? , meta_title = ? , description = ?, tag = ?, meta_description = ?, meta_keyword = ?");
			mysqli_stmt_bind_param($stmt, "iissssss", $oc_pid, $lid, $p['title'], $p['title'], $p['description'], $tag, $meta_desc, $meta_keyword);
			//mysqli_stmt_execute($stmt);
			if(!mysqli_stmt_execute($stmt))
			{
				//echo "\n$stmt\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
		}
		catch(\Exception $e)
		{
			print_r($e); 
			echo "\n product_id:".$p['id'];
			exit;
		}
		////
		$subcats = explode(',',$p['subcategory']);
		foreach($subcats as $subcat)
		{
			$sql_ocp = "insert into oc_product_to_category SET product_id = '".$oc_pid."', category_id = '".$subcat."'";
			if(!mysqli_query($mysqli, $sql_ocp))
			{
				echo "\n$sql_ocp\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
		}
		
		////
		
		$sql_ocpl = "insert into oc_product_to_layout SET store_id = 0, layout_id = 0, product_id = '".$oc_pid."'";
		if(!mysqli_query($mysqli, $sql_ocpl))
			{
				echo "\n$sql_ocpl\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
		
		////
		
		$sql_ocpl = "insert into oc_product_to_store SET  store_id = 0, product_id = '".$oc_pid."'";
		if(!mysqli_query($mysqli, $sql_ocpl))
			{
				echo "\n$sql_ocpl\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
		
		////
		
		$sql_ocpl = "update products SET  processed = 1 WHERE id = '".$p['id']."'";
		if(!mysqli_query($mysqli, $sql_ocpl))
			{
				echo "\n$sql_ocpl\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
		
	}
}