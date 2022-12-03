<?php
$pages = 0; //ceil($products_cnt->count/250); // Count products / 250
$page = $starting_page = 1;
 //https://www.hkarimbuksh.com/collections/groceries/products.json
$mysqli = mysqli_connect("localhost","root","","mobimark_grocery");

//include("simple_html_dom.php");
//$html = new simple_html_dom();

$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
$category = "groceries";
$shop_product_id = "";
$subcats_total = [];
$subcats_arr = [];
$products = [];	
while(true)
{
	
	//if($page >= $starting_page + 10)
	//{
	//	echo "\nDone my job at starting page was $starting_page now exit at page = $page\n";
	//	exit;
	//}
	
	//echo "\nPage NO: $page\n";
	$url = "https://hkarimbuksh.com/collections/" . $category . "/products.json?page=" . $page;
	echo "URL ===> $url\n";
	//echo "\n$url\n"; exit;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_VERBOSE, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, $agent);
	curl_setopt($ch, CURLOPT_URL,$url);
	$result=curl_exec($ch);
	$json = json_decode($result);
	//var_dump($json);
	
	if(count($json->products) == 0)
	{
		echo "\n** NO PRODUCTS FOUND **\n";
		//file_put_contents($category.".ids.txt", $shop_product_id);
		print_r($subcats_total);
		file_put_contents('total_cats.txt', implode(",",$subcats_total));
		break;
	}
	else  // products available
	{
		//print_r($json);
		for($i = 0; $i < count($json->products); $i++)
		{
			$cat = 'Medicine';
			$shop_product_id = (int)$json->products[$i]->id; //$shop_product_id . $json->products[$i]->id . ",";
			//echo $shop_product_id; exit;
			$tags = $json->products[$i]->tags;
			
			foreach($tags as $tag)
			{
				
				
				//$params = [ $specification, $description];
				//$id = (int) $row['id'];
				if(stripos($tag, 'brand') !== false  || stripos($tag, 'Nutrition') !== false  
					|| stripos($tag, 'Price') !== false 
				) { continue;  }
				//else { continue; }
				$tag = str_replace("'", "", $tag);
				
				//echo "$tag";
				$find_sql = "select * from oc_category_description where `name` like '%$tag%'";
				//echo "\n$find_sql\n"; 
				$find_result = mysqli_query($mysqli, $find_sql);
				if(mysqli_num_rows($find_result) > 0 )  //only one category allowed
				{
					$qresult = mysqli_fetch_array($find_result);
					//print_r($qresult);
					//$subcats_arr[] = $qresult['category_id'];
					//echo "\n $tag Category Found!!!!!!\n";
					//exit;
					//continue;
				}
				else {
					if(!isset($subcats_arr[$tag]))
					{
						$products[$tag] = [];
						$products[$tag][] = $shop_product_id;
						$subcats_arr[$tag] = 0;
					}
					else
					{
						$products[$tag][] = $shop_product_id;
						$subcats_arr[$tag]++;
					}
				}
				
				print_r($subcats_arr);
			}
			/*
			$find_sql = "select * from products where shopify_product_id = $shop_product_id and subcategory is NULL";
			$find_result = mysqli_query($mysqli, $find_sql);
			if(mysqli_num_rows($find_result) > 0)
			{
				
				//$result = mysqli_fetch_array($find_result);
				$subcats = implode(',', $subcats_arr);
				//$sql = "Update products set subcategory = '$subcats' Where shopify_product_id = $shop_product_id";
				//mysqli_query($mysqli, $sql);
				//echo "\n $tag Category Found!!!!!!\n";
				//exit;
				//continue;
				//echo "\nUpdating shopify id = $shop_product_id \n";
				//echo "\nsubcats: $subcats\n";
				if(count($subcats_arr) == 0)
				{
					$subcats_total = array_unique(array_merge($subcats_arr, $subcats_total), SORT_REGULAR);//array_merge($tags, $subcats_total);
					print_r($subcats_total);
					echo "\n======\n";
				}
			}
			*/
			//exit;
		}
		//exit;
	}
	$page++;
	//break;
}


//file_put_contents('products.txt', implode(",",$subcats_total));
$handle = fopen("products.txt","a");
foreach($products as $tag => $shop_ids)
{
	$string = $tag."\n==================================\n";
	$string .= implode(",", $shop_ids);
	$string .= "\n\n\n";
	fwrite($handle, $string);
}
//file_put_contents('total_cats.txt', implode(",",$subcats_total));

