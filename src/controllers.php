<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

use Page\Pagination;

$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());


$app['translator.domains'] = array(
    'messages' => array(
        'en' => array(
            'hello' => 'Hello %name%',
            'goodbye' => 'Goodbye %name%',
        ),
        'fr' => array(
            'hello' => 'Bonjour %name%',
            'goodbye' => 'Au revoir %name%',
        ),
    ),
    'validators' => array(
        'fr' => array(
            'not_numeric' => 'Cette valeur doit être un nombre.',
            'not_blank' => 'Cette valeur ne peut etre à blanc',
            'min_length' => 'Saisir au moins {{ limit }} caractères',
            'max_length' => 'Saisir au plus {{ limit }} caractères'
        ),
    ),
);

$app->before(
    function (Request $request) use ($app) {
        $app['translator']->setLocale($request->getPreferredLanguage(['en', 'fr']));
    }
);

//$app->get('/{_locale}/{message}/{name}', function ($message, $name) use ($app) {
//    return $app['translator']->trans($message, array('%name%' => $name));
//});

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage')
;

$app->get('/test/', function () use ($app) {
    return $app['twig']->render('test.html.twig', array());
})
->bind('testpage')
;

$app->get('/testparam/{id}', function ($id) use ($app) {
    return $app['twig']->render('testparam.html.twig', array(
                'param1' => $id,
                'titi' => 'gros minet'
    ));
})
->bind('testparam')
;

$app->get('/listebd/{id}', function ($id) use ($app) {
    require_once 'tempdata/liste_bd_temp.php';

    return $app['twig']->render('listebd.html.twig', array(
                'param1' => $id,
                'listebd' => getListeBD()
    ));
})
->bind('listebd')
;

$app->get('/listebd-crud/', function () use ($app) {
    require_once 'tempdata/liste_bd_temp.php';

    return $app['twig']->render('listebd-crud.html.twig', array(
                'listebd' => getListeBD()
    ));
})
->bind('listebd-crud')
;

$app->get('/listebd-pdf/', function () use ($app) {
    require_once 'tempdata/liste_bd_temp.php';
    $tmprows = getListeBD();
    // le composant tctable attend un tableau d’objets en entrée
    // alors on reformate notre liste pour qu’elle corresponde 
    // au format attendu (et on la stocke dans $rows)  
    $rows = array();
    foreach ($tmprows as $tmprow) {
        $tmpobj = new stdClass();
        $tmpobj->album = $tmprow['album'];
        $tmpobj->auteur = $tmprow['auteur'];
        $tmpobj->editeur = $tmprow['editeur'];
        $tmpobj->parution = $tmprow['parution'];
        $rows[] = $tmpobj;
    }
    $pdf = new \TCPDF();
    $minRowHeight = 6; //mm
    // on prépare les entête de colonne de notre tableau
    $tctable = new \voilab\tctable\TcTable($pdf, $minRowHeight);
    $tctable->setColumns([
        'titre' => [
            'isMultiLine' => true,
            'header' => 'Titre',
            'width' => 60
        // check inline documentation to see what options are available.
        // Basically, it's everything TCPDF Cell and MultiCell can eat.
        ],
        'auteur' => [
            'isMultiLine' => true,
            'header' => 'Auteur',
            'width' => 40,
        ],
        'editeur' => [
            'isMultiLine' => true,
            'header' => 'Editeur',
            'width' => 40,
        ],
        'parution' => [
            'header' => 'Parution',
            'width' => 10,
//            'align' => 'R'
        ]
    ]);

    // add a page so the content can be printed on something
    $pdf->AddPage();
    // draw body
    $tctable->addBody($rows, function (\voilab\tctable\TcTable $table, $row) {
        $change_rate = 0.8;
        // map row data to TcTable column definitions
        return [
            'titre' => $row->album,
            'auteur' => $row->auteur,
            'editeur' => $row->editeur,
            'parution' => $row->parution,
        ];
    });

    // Définition des entêtes HTTP qui vont permettre au navigateur
    // de comprendre que le document reçu est un PDF
    $nom_fichier = 'listeBD';
    $params = array();
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
        // Paramètres spécifiques à Internet Explorer
        $params['Pragma'] = 'public';
        $params['Expires'] = 0;
        $params['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';
        $params['Content-type'] = 'application-download';
        //   $params['Content-Length'] = $size;
        $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.pdf";
        $params['Content-Transfer-Encoding'] = 'binary';
    } else {
        // Paramètres qui fonctionnent bien sur Firefox et Chrome
        // $params['Content-type'] = 'application/pdf';
        $params['Content-type'] = 'application-download';
        // $params['Content-Length'] = $size;
        $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.pdf";
        $params['Pragma'] = 'no-cache';
        $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
        $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';
    }

    $response = new Response();
    foreach ($params as $paramkey => $paramval) {
        $response->headers->set($paramkey, $paramval);
    }
    $response->setStatusCode(200, 'OK');
    $response->sendHeaders();
    $response->sendContent($pdf->Output('tctable.pdf', 'I'));
    return $response;
})
->bind('listebd-pdf')
;
$app->get('/listebd-xml/', function () use ($app) {
    require_once 'tempdata/liste_bd_temp.php';
    $books = getListeBD();

    $doc = new DOMDocument("1.0", "UTF-8");

    $r = $doc->createElement("books");
    $doc->appendChild($r);

    foreach ($books as $book) {
        $b = $doc->createElement("book");
        $author = $doc->createElement("author");
        $author->appendChild(
                $doc->createTextNode($book['auteur'])
        );
        $b->appendChild($author);
        $title = $doc->createElement("title");
        $title->appendChild(
                $doc->createTextNode($book['album'])
        );
        $b->appendChild($title);
        $publisher = $doc->createElement("publisher");
        $publisher->appendChild(
                $doc->createTextNode($book['editeur'])
        );
        $b->appendChild($publisher);
        $datepub = $doc->createElement("publication_date");
        $datepub->appendChild(
                $doc->createTextNode($book['parution'])
        );
        $b->appendChild($datepub);
        $r->appendChild($b);
    }
    $xml_output = $doc->saveXML();
    // Définition des entêtes HTTP qui vont permettre au navigateur
    // de comprendre que le document reçu est un fichier XML
    $nom_fichier = 'listeBD';

    $params = array();
    $params['Content-type'] = 'application/xml; charset=UTF-8';
    //$params['Content-Disposition'] = "attachment; filename={$nom_fichier}.xml";
    $params['Pragma'] = 'no-cache';
    $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
    $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

    $response = new Response();
    foreach ($params as $paramkey => $paramval) {
        $response->headers->set($paramkey, $paramval);
    }
    $response->setStatusCode(200, 'OK');
    $response->setContent($xml_output);

    return $response;
})
->bind('listebd-xml')
;
$app->get('/listebd-json/', function () use ($app) {
    require_once 'tempdata/liste_bd_temp.php';
    $books = getListeBD();

    $json = json_encode($books);

    // Définition des entêtes HTTP qui vont permettre au navigateur
    // de comprendre que le document reçu est un fichier XML
    $nom_fichier = 'listeBD';

    $params = array();
    $params['Content-type'] = 'text/plain; charset=UTF-8';
    //$params['Content-Disposition'] = "attachment; filename={$nom_fichier}.json";
    $params['Pragma'] = 'no-cache';
    $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
    $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

    $response = new Response();
    foreach ($params as $paramkey => $paramval) {
        $response->headers->set($paramkey, $paramval);
    }
    $response->setStatusCode(200, 'OK');
    $response->setContent($json);

    return $response;
})
->bind('listebd-json')
;

$app->get('/listebd-csv/', function () use ($app) {
    require_once 'tempdata/liste_bd_temp.php';
    $books = getListeBD();

    $csv = 'titre;auteur;editeur;parution'.PHP_EOL;
    foreach ($books as $book) {
        $csvdata = array();
        foreach (array_values($book) as $tmpdata) {
            // c'est moche de faire un "utf8_decode" mais si on veut
            // que les utilisateurs puissent ouvrir facilement le fichier
            // avec Excel, on n'a pas le choix. 
            // (Libre Office sait importer de l'UTF-8 sans problème)
            $csvdata [] = '"'.trim(utf8_decode($tmpdata)).'"';
        }
        $csv .= implode($csvdata, ';'). PHP_EOL;
    }

    // Définition des entêtes HTTP qui vont permettre au navigateur
    // de comprendre que le document reçu est un fichier XML
    $nom_fichier = 'listeBD';

    $params = array();
    $params['Content-type'] = 'application/vnd.ms-excel';
    $params['Content-Disposition'] = "attachment; filename={$nom_fichier}.csv";
    $params['Pragma'] = 'no-cache';
    $params['Cache-Control'] = 'no-store, no-cache, must-revalidate';
    $params['Expires'] = 'Sat, 26 Jul 1997 05:00:00 GMT';

    $response = new Response();
    foreach ($params as $paramkey => $paramval) {
        $response->headers->set($paramkey, $paramval);
    }
    $response->setStatusCode(200, 'OK');
    $response->setContent($csv);

    return $response;
})
->bind('listebd-csv')
;
        
$app->get('/albumbd-not-found/', function () use ($app) {

    return $app['twig']->render('albumbdnotfound.html.twig', array());
})
->bind('albumotfound')
;
$app->get('/albumbd-register/', function () use ($app) {

    return $app['twig']->render('albumbdregister.html.twig', array());
})
->bind('albumbdregister')
;

$app->match('/albumupdate/{id}/{crud}', function (Request $request, Silex\Application $app) {

    $albumbdForm = new \Form\AlbumbdForm($app, $request);
    $form = $albumbdForm->getForm();

    if ($request->getMethod() == 'GET') {
        $id = (int) $request->get('id');
        $crud = strtoupper(strip_tags($request->get('crud')));
        $data = [];
        require_once 'tempdata/liste_bd_temp.php';
        $albums = getListeBD();
        if ($crud == 'C') {
            $data = array(
                'crud' => $crud,
                'id' => 0,
                'album' => '',
                'auteur' => '',
                'editeur' => '',
                'parution' => ''
            );
        } else {
            if (!array_key_exists($id, $albums)) {
                // redirection
                return $app->redirect($app['url_generator']
                                ->generate('albumotfound'));
            } else {
                foreach ($albums[$id] as $key => $value) {
                    if ($key == 'parution') {
                        // la date de parution doit être formatée selon le 
                        // format attendu par le plugin jQuery
                        $tmpdate = new DateTime($value);
                        $data[$key] = $tmpdate->format('Y-m-d');
                    } else {
                        $data[$key] = $app->escape($value);
                    }
                };
                // On ajoute la notion de CRUD à notre jeu de données 
                $data['crud'] = $crud;
            }
        }
    }

    if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            // redirection
            if (isset($_POST['form']['return'])) {
                return $app->redirect($app['url_generator']
                                ->generate('listebd-crud'));
            } else {
                return $app->redirect($app['url_generator']
                                ->generate('albumbdregister'));
            }
        } else {
            // Date de parution à reformater selon le format défini 
            // sur jQueryUI Datepicker
            $data['parution'] = $data['parution']->format('Y-m-d');
        }
    }
    return $app['twig']->render(
                    'albumbd-form.html.twig', array(
                'form' => $form->createView(),
                'data' => $data
    ));
})
->bind('albumupdate');

$app->match('/albumupdate-draft/{id}', function (Request $request, Silex\Application $app) {

    $form = $app['form.factory']->createBuilder(FormType::class)
//    ->add('crud', HiddenType::class, array(
//        'constraints' => array(new Assert\NotBlank())
//        ))
//    ->add('id', HiddenType::class, array(
//        'constraints' => array(new Assert\NotBlank())
//        ))
            ->add('album', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(),
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => array('class' => 'form-control')
            ))
            ->add('auteur', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(),
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => array('class' => 'form-control')
            ))
            ->add('editeur', TextType::class, array(
                'constraints' => array(new Assert\NotBlank(),
                    new Assert\Length(array('min' => 2)),
                    new Assert\Length(array('max' => 30))
                ),
                'attr' => array('class' => 'form-control')
            ))
            ->add('parution', DateType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'attr' => array('class' => 'form-control'),
                'widget' => 'single_text',
                // do not render as type="date", to avoid HTML5 date pickers
                'html5' => true,
                    // add a class that can be selected in JavaScript
                    //        'attr' => ['class' => 'js-datepicker'],
            ))
            ->add('save', SubmitType::class, array(
                'attr' => array('label' => 'Enregistrer', 'class' => 'btn btn-success'),
            ))
            ->add('reset', ResetType::class, array(
                'attr' => array('label' => 'Effacer', 'class' => 'btn btn-default'),
            ))
            ->getForm();

    if ($request->getMethod() == 'GET') {
        $id = (int) $request->get('id');
        $data = [];
        require_once 'tempdata/liste_bd_temp.php';
        $albums = getListeBD();

        if (!array_key_exists($id, $albums)) {
            // redirection vers une route spécifique si l'album n'existe pas
            return $app->redirect($app['url_generator']->generate('albumotfound'));
        } else {
            // Copie des données de l'album dans le tableau qui servira
            // à "peupler" le formulaire
            foreach ($albums[$id] as $key => $value) {
                $data[$key] = $app->escape($value);
            };
        }
    }

    if ($request->getMethod() == 'POST') {
        // les données envoyées par le formulaire sont réinjectées
        // dans le nouveau objet $form
        $form->handleRequest($request);
        // les données du formulaire sont récupérées dans un tableau
        // pour traitement ultérieur
        $data = $form->getData();
        // si le formulaire a été soumis et qu'aucune anomalie n'a été détectée
        if ($form->isSubmitted() && $form->isValid()) {
            if (isset($data['return'])) {
                return $app->redirect($app['url_generator']->generate('albumbdregister'));
            } else {
                return $app->redirect($app['url_generator']->generate('albumbdregister'));
            }
        } else {
            // si formulaire non soumis ou si anomalie, c'est reparti pour un tour
            error_log('formulaire BAD :(');
            error_log(var_export($data, true));
        }
    }
    // Affichage ou réaffichage du formulaire
    return $app['twig']->render(
                    'albumbd-form-draft.html.twig', array(
                'form' => $form->createView(),
                'data' => $data
    ));
})
->bind('albumupdate-draft');

$app->get('/dashboard-01/', function () use ($app) {

    $series =
        [[
            'name'=> 'Tokyo',
            'data'=> [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
        ], [
            'name'=> 'New York',
            'data'=> [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
        ], [
            'name'=> 'Berlin',
            'data'=> [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
        ], [
            'name'=> 'London',
            'data'=> [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
        ]];
    $mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jui',
                'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Déc'];
    
    return $app['twig']->render('dashboard.html.twig', array(
                'datas' => json_encode($series),
                'titre' => 'Température moyenne mensuelle',
                'mois'  => json_encode($mois),
    ));
})
->bind('dashboard-01')
;

$app->get('/liste-persons/', function () use ($app) {
    $sql = 'SELECT id, first_name, last_name, email, gender FROM persons';
    $persons = $app['db']->fetchAll($sql);

    return $app['twig']->render('persons-list.html.twig', array(
                'persons' => $persons
    ));
})
->bind('liste-persons')
;

$app->get('/liste-personsv2/{offset}', function ($offset) use ($app) {
    $offset = (int)$offset;
    $nbl_par_page = 5 ;
    
    // comptage du nombre de lignes total
    $sql2 = 'SELECT count(*) as comptage FROM persons';
    $nblines = $app['db']->fetchAssoc($sql2);
    
    $offset_sql = $offset - 1;

    $sql1 = "SELECT id, first_name, last_name, email, gender FROM persons limit $offset_sql, $nbl_par_page";;
    $persons = $app['db']->fetchAll($sql1);

    $barre_pagination = new \Pagination($nblines['comptage'], $offset, $nbl_par_page, '', array(), true);

    return $app['twig']->render('persons-listv2.html.twig', array(
            'persons' => $persons,
            'barre_pagination' => $barre_pagination->navBarBootstrap(),
            'offset' => $offset
    ));
})
->bind('liste-personsv2')
;
     
$app->match('/person-crud/{id}/{crud}', function (Request $request, Silex\Application $app) {

    $personForm = new \Form\PersonForm($app, $request);
    $form = $personForm->getForm();

    if ($request->getMethod() == 'GET') {
        $id = (int) $request->get('id');
        $crud = strtoupper(strip_tags($request->get('crud')));
        $data = [];
        if ($crud == 'C') {
            $data = array(
                'crud' => $crud,
                'id' => 0,
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'gender' => ''
            );
        } else {
            $sql = 'SELECT id, first_name, last_name, email, gender FROM persons WHERE id = ?';
            $person = $app['db']->fetchAssoc($sql, array($id));
            if (!$person) { // (!array_key_exists($id, $albums)) {
                // redirection
                return $app->redirect($app['url_generator']
                                ->generate('personnotfound'));
            } else {
                foreach ($person as $key => $value) {
                    $data[$key] = $app->escape($value);
                };
                // On ajoute la notion de CRUD à notre jeu de données 
                $data['crud'] = $crud;
            }
        }
    }

    if ($request->getMethod() == 'POST') {
        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {
            $sql_response = false;
            switch($data['crud']) {
                case 'C' : {
                    $sql_response = $app['db']->insert('persons', array(
                        'first_name'=>$data['first_name'],
                        'last_name'=>$data['last_name'],
                        'email'=>$data['email'],
                        'gender'=>$data['gender']
                    ));
                    break;
                }
                case 'U' : {
                    $sql_response = $app['db']->update('persons', array(
                        'first_name'=>$data['first_name'],
                        'last_name'=>$data['last_name'],
                        'email'=>$data['email'],
                        'gender'=>$data['gender']
                    ), array(
                        'id' => (int)$data['id'],
                    ));
                    break;
                }
                case 'D' : {
                    $sql_response = $app['db']->delete('persons', array(
                        'id'=>(int)$data['id']
                    ));
                    break;
                }
            }
                    
//            $session = $this->getRequest()->getSession();
//            $session->getFlashBag()->add('message', 'Article saved!');
            // redirection
            if (isset($_POST['form']['return'])) {
                return $app->redirect($app['url_generator']
                                ->generate('liste-persons'));
            } else {
                if ($data['crud'] == 'D') {
                    return $app->redirect($app['url_generator']
                                ->generate('persondeleted'));
                } else {
                    return $app->redirect($app['url_generator']
                                ->generate('personregistered'));                    
                }
            }
        }
    }
    return $app['twig']->render(
            'person-form.html.twig', array(
                'form' => $form->createView(),
                'data' => $data
    ));
})
->bind('person-crud');

$app->get('/personregistered/', function () use ($app) {

    return $app['twig']->render('personregistered.html.twig', array());
})
->bind('personregistered')
;

$app->get('/persondeleted/', function () use ($app) {

    return $app['twig']->render('persondeleted.html.twig', array());
})
->bind('persondeleted')
;
        
$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/' . $code . '.html.twig',
        'errors/' . substr($code, 0, 2) . 'x.html.twig',
        'errors/' . substr($code, 0, 1) . 'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
