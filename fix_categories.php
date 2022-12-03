<?php

$mysqli = mysqli_connect("localhost","root","","mobimark_grocery");
$sql = "Select * from products  WHERE category <> 'Groceries'";  // try with olnaaj

$result_m = mysqli_query($mysqli, $sql);
if(mysqli_num_rows($result_m) > 0)
{
	//;
	
	while($p = mysqli_fetch_array($result_m))
	{
		//unset($p['description']);unset($p['description']);
		//print_r($p); exit;
		$subcats = explode(',',$p['subcategory']);
		if(!is_array($subcats)) { print_r($subcats); exit; }
		foreach($subcats as $subcat)
		{
			//print_r($p);
			$sql_ocp = "select * from oc_category_to_layout WHERE category_id = '".$subcat."'";
			$result = mysqli_query($mysqli, $sql_ocp);
			if(!$result)
			{
				echo "\n$sql_ocp\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
			if(mysqli_num_rows($result) == 0)
			{
				$sql_oc = "insert into oc_category_to_layout SET category_id = '$subcat', store_id = 0, layout_id = 0";
				//echo $sql_oc;
				
				
					if(!mysqli_query($mysqli, $sql_oc))
					{
						echo "\n$sql_oc\n";
						echo("Error description: " . mysqli_error($mysqli));
					}
					
				
			}
			else
			{
				echo "Found $subcat in oc_category_to_layout \n";
			}
			
			$sql_ocp = "select * from oc_category_to_store WHERE category_id = '".$subcat."'";
			$result = mysqli_query($mysqli, $sql_ocp);
			if(!$result)
			{
				echo "\n$sql_ocp\n";
				echo("Error description: " . mysqli_error($mysqli));
				exit;
			}
			if(mysqli_num_rows($result) == 0)
			{
				$sql_oc = "insert into oc_category_to_store SET category_id = '$subcat', store_id = 0";
				//echo $sql_oc;
				
				
					if(!mysqli_query($mysqli, $sql_oc))
					{
						echo "\n$sql_oc\n";
						echo("Error description: " . mysqli_error($mysqli));
					}
					
				
			}
			else
			{
				echo "Found $subcat in oc_category_to_store \n";
			}
		}
	}
		
}