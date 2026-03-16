```php
<?php
include("ligacao.php");

if(isset($_GET['id'])){

$id = intval($_GET['id']);

$sql = "UPDATE marcacoes SET estado='cancelado' WHERE id=$id";

$conn->query($sql);

}

header("Location: dashboard_barbeiro.php");
exit;
?>
```
