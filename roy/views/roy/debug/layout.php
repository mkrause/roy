<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Error</title>
    <style type="text/css">
body {
    font: 16px/1.4em "Book Antiqua", Georgia, Palatino, Times, "Times New Roman", serif;
    background: #8E9BAD; /*#485A6E*/
    color: #333;
}

a:link, a:visited {
    color: #485A6E;
    font-weight: bold;
}

a:hover {
    color: #6792AB;
}

h1, h2, h3 {
    font-family: "Palatino Linotype", "Book Antiqua", Palatino, serif;
    line-height: 1.4em;
}

h2 {
    font-size: 1.2em;
}

h3 {
    margin: 0;
    margin-top: 10px;
}

#content {
    margin: 80px auto 20px auto;
    padding: 20px 50px;
    width: 900px;
    background: white;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    border-radius: 5px;
    -moz-box-shadow: 2px 2px 4px black;
    -webkit-box-shadow: 2px 2px 2px black;
    box-shadow: 2px 2px 2px black;
}

.exception-details {
    padding: 15px 20px;
    background: #eee;
    border: 1px solid #bbb;
}

.exception-details p {
    margin: 2px 0;
}

.exception-details pre {
    overflow: auto;
}
    </style>
</head>
<body>

<div id="content">
<?= $content_output ?> 
</div>

</body>
</html>