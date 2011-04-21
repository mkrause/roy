<!DOCTYPE html>
<html>
<head>
    <title><?= html::encode(Example::title($this)) ?></title>
    <meta charset="utf-8">
</head>
<body>

<div id="container">
    <h1>Example Application</h1>
<?= $content_output ?> 
</div>

</body>
</html>
