<?php  session_start();
$userId=$_SESSION['USER_ID'];
include ('doctype.php');
include ('header.php');
//suppression du compte utilistrice
if(isset($_POST['userId'])) {
    $deleteSQL = $mysqli->prepare("DELETE FROM users WHERE id=?");
    $deleteSQL->bind_param('i', $_POST['userId']);
    $deleteSQL->execute() or die(print_r($mysqli->errorInfo()));
    //il faudra aussi d'autres requêtes pour supprimer les posts liés au user
    session_destroy();
    header("Location: home.php");
} else {
    echo('Il faut un identifiant utilisatrice valide pour supprimer votre compte !');
};
?>

<div id="wrapper" class='profile'> 
    <aside>
        <?php include ('photo.php');?>
    </aside>
    <main>
        <?php
        /**
        * Etape 1: Les paramètres concernent une utilisatrice en particulier
        * La première étape est donc de trouver quel est l'id de l'utilisatrice
        * Celui ci est indiqué en parametre GET de la page sous la forme user_id=...
        * Documentation : https://www.php.net/manual/fr/reserved.variables.get.php
        * ... mais en résumé c'est une manière de passer des informations à la page en ajoutant des choses dans l'url
        */
        /**
        * Etape 3: récupérer le nom de l'utilisateur
        */
        $laQuestionEnSql = "
            SELECT users.*, 
            count(DISTINCT posts.id) as totalpost, 
            count(DISTINCT given.post_id) as totalgiven, 
            count(DISTINCT recieved.user_id) as totalrecieved 
            FROM users 
            LEFT JOIN posts ON posts.user_id=users.id 
            LEFT JOIN likes as given ON given.user_id=users.id 
            LEFT JOIN likes as recieved ON recieved.post_id=posts.id 
            WHERE users.id = '$userId' 
            GROUP BY users.id
            ";
        $lesInformations = $mysqli->query($laQuestionEnSql);
        if (! $lesInformations)
        {
            echo("Échec de la requete : " . $mysqli->error);
        }
        $user = $lesInformations->fetch_assoc();
        /**
        * Etape 4: à vous de jouer
        */
        //@todo: afficher le résultat de la ligne ci dessous, remplacer les valeurs ci-après puiseffacer la ligne ci-dessous
        //echo "<pre>" . print_r($user, 1) . "</pre>";
        ?>                
            <article class='parameters'>
                <dl>
                    <dt>Pseudo</dt><br/>
                    <dd><a href=<?php $userId = $user['id']; echo "'wall.php?user_id=$userId'" 
                    ?>><?php echo($user['alias']) ?></a></dd>
                    <dt>Email</dt><br/>
                    <dd><?php echo($user['email']) ?></dd>
                    <dt>Nombre de messages</dt><br/>
                    <dd><?php echo($user['totalpost']) ?></dd>
                    <dt>Nombre de 💪  donnés </dt><br/>
                    <dd><?php echo($user['totalgiven']) ?></dd>
                    <dt>Nombre de 💪 reçus</dt><br/>
                    <dd><?php echo($user['totalrecieved']) ?></dd>
                    <dt>Modifier votre profil</dt><br/>
                    <dd>oui non</dd>
                    <dt>Ajouter une photo</dt><br/>
                    <dd>Mettre à jour votre photo de profil</dd>
                    <dt>Supprimer mon compte</dt><br />
                    <dd><a href="#delete" data-bs-toggle="modal" data-bs-target="#delete">Supprimer toutes mes informations</a></dd>
                </dl>
                <!-- Boîte de dialogue pour supprimer son compte-->
                <form action="" method="post">
                    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="modalLabel">Oui ! je veux supprimer mon compte !</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="visually-hidden">
                                    <label for='userId' class="form-label">User id</label>
                                    <input type="hidden" class="form-control" id="userId" name="userId" value="<?php echo($userId);?>">
                                </div>
                                <div class="modal-body">
                                    <button type="submit" class="btn btn-success" data-bs-dismiss="modal">GO !</button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Annuler</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>    
    </main>
</div>
