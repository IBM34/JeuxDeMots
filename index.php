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
            <center><a href="index.php"><img src="./img/logo.jpg" class="logo"/></a></center>
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

            <center>
           
            <p>Ce site internet a été réalisé pour le projet "Navigateur dans le réseau JeuxDeMots"</p>
         
            <p>Membres du groupe :</p>
         
            <p>- Ivan BRUNET-MANQUAT</p>
            <p>- Mattéo COQUILHAT</p>
            <p>- Gualtiero LUGATO </p>
         
         
         <p>Pour rechercher un terme et ses relations, il vous suffit de le saisir dans la barre de recherche située dans le menu.</p>
         
         <p>La recherche est facilitée par un systême d'autocomplétion </p>
         
         <p>Le site internet dispose d'un systême de cache afin d'afficher instantanément des résultats pour les recherches déja effectuées par le passé.</p>
         
         <p>Lorqu'un utilisateur effectue une recherche dont le résultat en cache date de plus de 15 jours, une mise à jour de ces résultats est planifiée pour la nuit.</p>
         
         <p>Le site est "responsive" grâce à l'utilisation du framework bootstrap.</p>
         
         
          <img src="img/responsive.png" style="width: 90%;
    height: auto;" />
         
            </center>
         
        </div>
        
        
        
        
        <script src="./js/jquery.min.js"></script>
        <script src="./js/jquery-ui.js"></script>
        <script src="./js/bootstrap.min.js"></script>
        <script type="text/javascript" src="./js/autocomplete.js"></script> 
        <script type="text/javascript" src="./js/navbar.js"></script>
    </body>
</html>
