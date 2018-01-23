<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" /> 
        <title>JDMClient</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="./css/bootstrap.min.css">
        <link rel="stylesheet" href="./css/jquery-ui.css">
        <link rel="stylesheet" href="./css/style.css">

    </head>

    <body>

        <div class="header">
            <center><a href="index.php"><img src="./img/logo.jpg" style="width: 40%; height: 40%"/></a></center>
        </div>

        <div id="navbar">

            <a class="active" href="index.php">Accueil</a>

            <form autocomplete="off" class="searchForm"  action="results.php">
                <div class="input-group">
                    <input name="terme" id="terme" type="text" class="form-control" placeholder="Entrez un terme" name="q">
                    <div class="input-group-btn">
                        <button type="submit" class="btn btn-success" type="button">Rechercher</button>
                    </div>
                </div>
            </form>

        </div>

        <div class="content">




        </div>
        <script src="./js/jquery.min.js"></script>
        <script src="./js/jquery-ui.js"></script>
        <script src="./js/bootstrap.min.js"></script>
        <script type="text/javascript" src="./js/autocomplete.js"></script> 
    </body>
</html>
