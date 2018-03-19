# Direct-database

## Easy to use

A simple database for optimum data. It is intended for those who do not want to create tables and write queries and mysql.

### Create and connect 

Syntax
```
$database = new Database([ base name ],[ username ],[ password ])
```
Example
```
$base = new Database("mydatabase","myusername","mypassword");
```
### Insert
If the table is not created the insert method will create it. Just enter the column names and values

Syntax
```
$table = "table name";
$values = ["" => "value1","row2" => "value2","row3" => "value3" ];
$base->insert($table,$values);
```
Example
```
$table = "user"; // table name
$values = ["name" => "John","surname" => "Wain","age" => "23" ];
$base->insert($table,$values);
```

### Select 

Syntax
```
$table = "table name";  
$condition='$row->id == "value" '; // '' for no condition 
$order = 'd'; //descedenting order, '' ascedenting order
$start = offset;
$per_page = limit; // for all data $per_page = "";

$query = $base->select($table,$condition,$order,$start,$per_page);

echo "Results - select : ".$base->num_rows();
echo "<hr>";
foreach($query as $row){
	
	echo "Id : ".$row->id." row1 : ".$row->row1." row2 : ".$row->row2." row3 : ".$row->row3;
	echo "<br>";
	
}
```
Example
```
$table = "user"; // table name
$condition='$row->id == 0 '; // '' for no condition 
$order = 'd'; //descedenting order, '' ascedenting order
$start = 0;
$per_page = 10; // for all data $per_page = "";

$query = $base->select($table,$condition,$order,$start,$per_page);

echo "Results - select : ".$base->num_rows();
echo "<hr>";
foreach($query as $row){
	
	echo "Id : ".$row->id." name : ".$row->name." surname : ".$row->surname." age : ".$row->age;
	echo "<br>";
	
}
```

### Update

Syntax
```
$table = "table name"; 
$condition='$row->row == "value"';
$values = ["row1" => "value1","row2" => "value2" ]; 

$base->update($table,$condition,$values);
```
Example 1
```
$table = "user";
$condition='$row->id == 0';
$values = ["name" => "Johny","surname" => "Wainy" ]; 

$base->update($table,$condition,$values);
```
Example 2
```
$table = "user";
$condition='$row->id>-1'; // Update all
$values = ["name" => "Johny","surname" => "Wainy" ]; 

$base->update($table,$condition,$values);
```

### Delete

Syntax
```
$condition='$row->row == value';
$base->delete("table",$condition);
```
Example 1
```
$condition='$row->id == 1';
$base->delete("table",$condition);
```
Example 2
```
$condition='$row->name == "John"';
$base->delete("table",$condition);
```
Example 3
```
$condition='$row->id > -1'; Delete all
$base->delete("table",$condition);
```

### Search

Syntax
```
$table = "table name"; 
$rows= "row1,row2,row3"; // Rows name to be searched
$search = trim($posteddata); // Search query
$order = 'd'; 'd' descedenting order, '' ascedenting order
$start = offset; // Offset
$per_page = limit; // for all data $per_page = "";

$query = $base->search($table,$rows,$search,$order,$start,$per_page);

echo "Results : ".$base->num_rows();
echo "<hr>";
foreach($query as $row){
	
	echo "Id : ".$row->id." row1 : ".$row->row1." row2 : ".$row->row2." row3 : ".$row->row3;
	echo "<br>";
	
}
```
Example
```
$table = "user"; // table name
$rows= "name,surname,age"; // Rows name to be searched
$search = trim($_POST['name']); // Search query
$order = 'd'; 'd' descedenting order, '' ascedenting order
$start = 0; // Offset
$per_page = 10; // for all data $per_page = "";

$query = $base->search($table,$rows,$search,$order,$start,$per_page);

echo "Results : ".$base->num_rows();
echo "<hr>";
foreach($query as $row){
	
	echo "Id : ".$row->id." name : ".$row->name." surname : ".$row->surname." age : ".$row->age;
	echo "<br>";
	
}
```