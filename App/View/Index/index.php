<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <h1><?=$uuid?></h1>
  <form action="?issubmit=1" method="post" enctype="multipart/form-data">
    <input type="file" name="file"/>
    <input type="submit" value="post">
  </form>
</body>
</html>