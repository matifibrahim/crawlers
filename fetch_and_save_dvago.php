<?php
$pages = 0; //ceil($products_cnt->count/250); // Count products / 250
$page = $starting_page = 150;
 //https://www.hkarimbuksh.com/collections/groceries/products.json
$mysqli = mysqli_connect("localhost","root","","farhan_scraping");

include("simple_html_dom.php");
$html = new simple_html_dom();

$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
while(true)
{
	
	if($page >= $starting_page + 10)
	{
		echo "\nDone my job at starting page was $starting_page now exit at page = $page\n";
		exit;
	}
	
	echo "\nPage NO: $page\n";
	$url = "https://dvago.pk/collections/medicine/products.json?page=" . $page;
	//echo "\n$url\n"; exit;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL,$url);
	$result=curl_exec($ch);
	$json = json_decode($result);
	//var_dump($json);
	if(count($json->products) == 0)
	{
		echo "\n** NO PRODUCTS FOUND **\n";
		break;
	}
	else  // products available
	{
		//print_r($json);
		for($i = 0; $i < count($json->products); $i++)
		{
			$cat = 'Medicine';
			$shop_product_id = $json->products[$i]->id;
			$title = $json->products[$i]->title;
			echo "\nFetching Title:$title\n";
			$description = $json->products[$i]->body_html;
			$html->load($description);
			$description = $html->find('#tab-1', 0);
			$description .= $html->find('#tab-2', 0);
			//exit;
			//str_ireplace('<strong>Disclaimer</strong>', 
			
			
			//echo "\n$description\n"; exit;
			$tags = $json->products[$i]->tags;
			$brand = $json->products[$i]->vendor;
			$variant_0 = $json->products[$i]->variants[0];
			if(stripos($variant_0->title, 'Price') !== false)
			{
				$variant_title = str_ireplace('Price', '', $variant_0->title);
				$title = trim($title . ' - ' . $variant_title);
			}
			$price = number_format((float)$variant_0->price, 2, '.', '');
			$grams = (int)$variant_0->grams;
			$find_sql = "select id from products where shopify_product_id = $shop_product_id";
			$find_result = mysqli_query($mysqli, $find_sql);
			if(mysqli_num_rows($find_result) > 0)
			{
				continue;
			}
			if($brand == "")
			{
				foreach($tags as $tag)
				{
					if(strpos($tag,'Brand') !== false)
					{
						$brand = explode('_', $tag)[1];
					}
				}
			}
			
			$update_sql = "insert into products SET category = ?, shopify_product_id = ?, title = ?, price = ?, grams = ?,  description = ? , brand = ?";
			$stmt = mysqli_prepare($mysqli, $update_sql);
			mysqli_stmt_bind_param($stmt, "sisdiss", $cat, $shop_product_id, $title, $price, $grams, $description, $brand);
			//mysqli_stmt_bind_param($stmt, "s", $description);
			$id = mysqli_stmt_execute($stmt);
			$pid = mysqli_insert_id($mysqli); //exit;
			if($pid == 0)
			{
				
				echo "\nError!!!!!!!\n";
				echo mysqli_error($mysqli)."\n";
				echo "\ncat:$cat|shop_product_id:$shop_product_id|title:$title|price:$price|grams:$grams|brand:$brand|description:$description|\n";
				exit;
			}
			$images = $json->products[$i]->images;
			$j = 0;
			foreach($images as $image)
			{
				$src = $image->src;
				echo "\n$src\n";
				$image_contents = file_get_contents($src);
				$ext = strpos($src, '.png') ? '.png' : '.jpg';
				$image_name = $shop_product_id . '_' . $j . $ext;
				file_put_contents('./images/' . $image_name, $image_contents);
				
				$update_sql = "insert into product_images SET product_id = ?, image_name = ?, shopify_src = ?";
				$stmt = mysqli_prepare($mysqli, $update_sql);
				mysqli_stmt_bind_param($stmt, "iss", $pid, $image_name, $src);
				//mysqli_stmt_bind_param($stmt, "s", $description);
				mysqli_stmt_execute($stmt);
				$j++;
			}
			//exit;
			/*
			if($i > 3)
			{
				echo "\n Exiting at i = $i \n"; exit;
			}
			*/
		}
		//exit;
	}
	$page++;
	
}

