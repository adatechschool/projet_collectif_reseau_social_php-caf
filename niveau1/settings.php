<?php session_start();
$userId=$_SESSION['USER_ID'];
include ('doctype.php');
include('connection.php');
//suppression du compte utilisatrice
if(isset($_POST['userId'])) {
    $userIdInPosts = $mysqli->query("SELECT id FROM posts where user_id=$userId");
    while ($id = $userIdInPosts->fetch_assoc())
        {
            $deleteInPostsTags = $mysqli->prepare("DELETE FROM posts_tags WHERE post_id=?");
            $deleteInPostsTags->bind_param('i', $id['id']);
            $deleteInPostsTags->execute() or die(print_r($mysqli->errorInfo()));
            $deleteInPostsTags->close();
        }
    $deleteInPosts = $mysqli->prepare("DELETE FROM posts WHERE user_id=?");
    $deleteInPosts->bind_param('i', $_POST['userId']);
    $deleteInPosts->execute() or die(print_r($mysqli->errorInfo()));
    $deleteInPosts->close();
    $deleteInUsers = $mysqli->prepare("DELETE FROM users WHERE id=?");
    $deleteInUsers->bind_param('i', $_POST['userId']);
    $deleteInUsers->execute() or die(print_r($mysqli->errorInfo()));
    $deleteInUsers->close();
    //il faudra aussi d'autres requêtes pour supprimer les posts liés au user
    session_destroy();
    header("Location: home.php");
}
?>
    <body>
        <?php
            include ('header.php');
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
                    <article id="parameters" class='parameters'>
                        <dl>
                            <dt>Pseudo</dt>
                            <dd><a href=<?php $userId = $user['id']; echo "'wall.php?user_id=$userId'" 
                            ?>><?php echo($user['alias']) ?></a></dd>
                            <dt>Email</dt>
                            <dd><?php echo($user['email']) ?></dd>
                            <dt>Nombre de messages</dt>
                            <dd><?php echo($user['totalpost']) ?></dd>
                            <dt>Nombre de 💪  donnés </dt>
                            <dd><?php echo($user['totalgiven']) ?></dd>
                            <dt>Nombre de 💪 reçus</dt>
                            <dd><?php echo($user['totalrecieved']) ?></dd>
                            <dt>Modifier votre profil</dt>
                            <dd>oui non</dd>
                            <dt>Ajouter une photo</dt>
                            <dd>Mettre à jour votre photo de profil</dd>
                            <dt>Supprimer mon compte</dt>
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
    </body>
</html>


