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

$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());


$app['translator.domains'] = array(
    'messages' => array(
        'en' => array(
            'hello'     => 'Hello %name%',
            'goodbye'   => 'Goodbye %name%',
        ),
        'fr' => array(
            'hello'     => 'Bonjour %name%',
            'goodbye'   => 'Au revoir %name%',
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
    return $app['twig']->render('testparam.html.twig', 
            array(
                'param1' => $id,
                'titi' => 'gros minet'               
            ));
})
->bind('testparam')
;

$app->get('/listebd/{id}', function ($id) use ($app) {
    require_once 'tempdata/liste_bd_temp.php';
    
    return $app['twig']->render('listebd.html.twig', 
            array(
                'param1' => $id,
                'listebd' => getListeBD()
            ));
})
->bind('listebd')
;

$app->get('/listebd-crud/', function () use ($app) {
    require_once 'tempdata/liste_bd_temp.php';
    
    return $app['twig']->render('listebd-crud.html.twig', 
            array(
                'listebd' => getListeBD()
            ));
})
->bind('listebd-crud')
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

$app->match('/albumupdate/{id}/{crud}', 
        function (Request $request, Silex\Application $app) {
    
    $albumbdForm = new \Form\AlbumbdForm($app, $request);
    $form = $albumbdForm->getForm();
    
    if($request->getMethod() == 'GET'){ 
        $id = (int)$request->get('id');
        $crud = strtoupper(strip_tags($request->get('crud')));
        $data = [] ;
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
                foreach($albums[$id] as $key=>$value) {
                    if ($key == 'parution') {
                        // la date de parution doit être formatée selon le 
                        // format attendu par le plugin jQuery
                        $tmpdate = new DateTime($value);
                        $data[$key] = $tmpdate->format('Y/m/d');
                    } else {
                        $data[$key] = $app->escape($value);
                    }
                };
                // On ajoute la notion de CRUD à notre jeu de données 
                $data['crud'] = $crud ;
            }
        }
    }
   
    if($request->getMethod() == 'POST'){    
        $form->handleRequest($request);
        $data = $form->getData();
        if ($form->isSubmitted() && $form->isValid()) {

//    $user = new User();
//    $user->setUsername($data['username']);
//    $user->setEmail($data['email']);
//    $user->setPassword($this->encodePassword($user, $data['password']));
//
//    $em = $this->getDoctrine()->getManager();
//    $em->persist($user);
//    $em->flush();
   
//            $session = $this->getRequest()->getSession();
//            $session->getFlashBag()->add('message', 'Article saved!');
            
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
            $data['parution'] = $data['parution']->format('Y/m/d');         
        }
    }
    return $app['twig']->render(
    'albumbd-form.html.twig',
    array(
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
        'attr' => array('class'=>'form-control')        
    ))
    ->add('auteur', TextType::class, array(
        'constraints' => array(new Assert\NotBlank(), 
            new Assert\Length(array('min' => 2)),
            new Assert\Length(array('max' => 30))
        ),
        'attr' => array('class'=>'form-control')        
    ))
    ->add('editeur', TextType::class, array(
        'constraints' => array(new Assert\NotBlank(), 
            new Assert\Length(array('min' => 2)),
            new Assert\Length(array('max' => 30))
        ),
        'attr' => array('class'=>'form-control')        
    ))
    ->add('parution', DateType::class, array(
        'constraints' => array(new Assert\NotBlank()),
        'attr' => array('class'=>'form-control'),
        'widget' => 'single_text',

        // do not render as type="date", to avoid HTML5 date pickers
        'html5' => true,

        // add a class that can be selected in JavaScript
        //        'attr' => ['class' => 'js-datepicker'],
    ))
    ->add('save', SubmitType::class, array(
        'attr' => array('label' => 'Enregistrer', 'class'=>'btn btn-success'),
    ))
    ->add('reset', ResetType::class, array(
        'attr' => array('label' => 'Effacer', 'class'=>'btn btn-default'),
    ))
    ->getForm();
    
    if($request->getMethod() == 'GET'){ 
        $id = (int)$request->get('id');
        $data = [] ;
        require_once 'tempdata/liste_bd_temp.php';
        $albums = getListeBD();

        if (!array_key_exists($id, $albums)) {
            // redirection vers une route spécifique si l'album n'existe pas
            return $app->redirect($app['url_generator']->generate('albumotfound'));
        } else {
            // Copie des données de l'album dans le tableau qui servira
            // à "peupler" le formulaire
            foreach($albums[$id] as $key=>$value) {
                $data[$key] = $app->escape($value);
            };
        }

    }
   
    if($request->getMethod() == 'POST'){ 
        // les données envoyées par le formulaire sont réinjectées
        // dans le nouveau objet $form
        $form->handleRequest($request);
        // les données du formulaire sont récupérées dans un tableau
        // pour traitement ultérieur
        $data = $form->getData();
        // si le formulaire a été soumis et qu'aucune anomalie n'a été détectée
        if ($form->isSubmitted() && $form->isValid()) {
            error_log('formulaire OK :)');
            error_log(var_export($data, true));
            if (isset($data['return'])) {
                error_log('redirection vers la liste des albums');
                return $app->redirect($app['url_generator']->generate('albumbdregister'));                
            } else {
                error_log('redirection vers la confirmation de mise à jour (ou suppression)');
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
    'albumbd-form-draft.html.twig',
    array(
        'form' => $form->createView(),
        'data' => $data
    ));

})
->bind('albumupdate-draft');

$app->match('/albumupdatex/{id}', function(Request $request, $id) use ($app){

    // formulaire
    $albumbdType = new \Form\AlbumbdType();
    $form = $app['form.factory']->create(\Form\AlbumbdType::class);
    $data = null ;
    if($request->getMethod() == 'GET'){
        $id = (int)$id; 
        require_once 'tempdata/liste_bd_temp.php';
        $albums = getListeBD();
        if (!array_key_exists($id, $albums)) {
            // redirection
            return $app->redirect($app['url_generator']->generate('albumbd-not-found'));
        } else {
            $data = $albums[$id];
        }
    }
    if($request->getMethod() == 'POST'){
        //$form->handleRequest($request);
        $form->bind($request);
        if($form->isValid()){

            // données
            $data = $form->getData();

            // redirection
            return $app->redirect($app['url_generator']->generate('albumbdregister'));
        }
    }

    return $app['twig']->render(
        'albumbd-form.html.twig',
        array(
            'form' => $form->createView(),
//            'data' => $data
        ));
})
->bind('albumupdatex')
;

// contact
$app->match('/contact/', function(Request $request) use ($app){

//    $albumbdType = new \Form\AlbumbdType();
//    $form = $app['form.factory']->create(\Form\AlbumbdType::class);
	// formulaire
	$contactType = new \Form\ContactType();
	$form = $app['form.factory']->create(\Form\ContactType::class);

	// vérification
	if($request->getMethod() == 'POST'){
		$form->bind($request);
		if($form->isValid()){

			// données
			$data = $form->getData();

			// envoi par mail
			$message = \Swift_Message::newInstance();
			$message
				->setFrom($data['mail'])
				->setTo('VOTRE.MAIL')
				->setSubject("Informations d'un contact")
				->setBody(
					$app['twig']->render('mail.txt.twig', array('data' => $data))
				)
			;

			$app['mailer']->send($message);

			// redirection
			return $app->redirect($app['url_generator']->generate('contact-confirmation'));

		}
	}

	return $app['twig']->render(
				'contact.html.twig',
				array(
					'form' => $form->createView()
				));
})
->bind('contact')
;

// confirmation de contact
$app->get('/contact-confirmation', function() use($app){
	return $app['twig']->render('contact-confirmation.html.twig');
})
->bind('contact-confirmation')
;

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
