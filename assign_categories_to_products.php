<?php
$pages = 0; //ceil($products_cnt->count/250); // Count products / 250
$page = $starting_page = 1;
 //https://www.hkarimbuksh.com/collections/groceries/products.json
$mysqli = mysqli_connect("localhost","root","","mobimark_grocery");

//include("simple_html_dom.php");
//$html = new simple_html_dom();

$agent= 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
$category = "medicine";
$shop_product_id = "";
while(true)
{
	
	//if($page >= $starting_page + 10)
	//{
	//	echo "\nDone my job at starting page was $starting_page now exit at page = $page\n";
	//	exit;
	//}
	
	//echo "\nPage NO: $page\n";
	$url = "https://www.dvago.pk/collections/" . $category . "/products.json?page=" . $page;
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
			$subcats_arr = [];
			foreach($tags as $tag)
			{
				
				
				//$params = [ $specification, $description];
				//$id = (int) $row['id'];
				$find_sql = "select * from oc_category_description where `name` = '$tag'";
				$find_result = mysqli_query($mysqli, $find_sql);
				if(mysqli_num_rows($find_result) > 0)
				{
					$qresult = mysqli_fetch_array($find_result);
					//print_r($qresult);
					$subcats_arr[] = $qresult['category_id'];
					//echo "\n $tag Category Found!!!!!!\n";
					//exit;
					//continue;
				}
				
				
			}
			
			$find_sql = "select * from products where shopify_product_id = $shop_product_id";
			$find_result = mysqli_query($mysqli, $find_sql);
			if(mysqli_num_rows($find_result) > 0)
			{
				//$result = mysqli_fetch_array($find_result);
				$subcats = implode(',', $subcats_arr);
				$sql = "Update products set subcategory = '$subcats' Where shopify_product_id = $shop_product_id";
				mysqli_query($mysqli, $sql);
				//echo "\n $tag Category Found!!!!!!\n";
				//exit;
				//continue;
			}
			//exit;
		}
		//exit;
	}
	$page++;
	
}

