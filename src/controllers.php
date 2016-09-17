<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//use Symfony\Component\Form\Extension\Core\Type\FormType;
//use Symfony\Component\Form\Extension\Core\Type\TextType;
//use Symfony\Component\Form\Extension\Core\Type\HiddenType;
//use Symfony\Component\Form\Extension\Core\Type\EmailType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
//use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
//use Symfony\Component\Form\Extension\Core\Type\ButtonType;
//use Symfony\Component\Form\Extension\Core\Type\ResetType;
//use Symfony\Component\Form\Extension\Core\Type\SubmitType;
//use Symfony\Component\Validator\Constraints as Assert;

$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));


//Request::setTrustedProxies(array('127.0.0.1'));


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
                'param1' => $id
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

//$app->get('/albumupdate/{id}', function ($id) use ($app) {
//    require_once 'tempdata/liste_bd_temp.php';
//    $albums = getListeBD();
//    if (!array_key_exists($id, $albums)) {
//        return false ;
//    }
//    return $app['twig']->render('albumbd-form.html.twig', 
//            array(
//                'album' => $albums[$id]
//            ));
//})
//->bind('albumupdate')
//;

$app->match('/albumupdate/{id}/{crud}', function (Request $request, Silex\Application $app) {
    
    $albumbdForm = new \Form\AlbumbdForm($app, $request);
    $form = $albumbdForm->getForm();
    
//    $form = $app['form.factory']->createBuilder(FormType::class)
//    ->add('crud', HiddenType::class, array(
//        'constraints' => array(new Assert\NotBlank())
//        ))
//    ->add('id', HiddenType::class, array(
//        'constraints' => array(new Assert\NotBlank())
//        ))
//    ->add('album', TextType::class, array(
//        'constraints' => array(new Assert\NotBlank(), 
//            new Assert\Length(array('min' => 2)),
//            new Assert\Length(array('max' => 30))
//        ),
//        'attr' => array('class'=>'form-control')        
//    ))
//    ->add('auteur', TextType::class, array(
//        'constraints' => array(new Assert\NotBlank(), 
//            new Assert\Length(array('min' => 2)),
//            new Assert\Length(array('max' => 30))
//        ),
//        'attr' => array('class'=>'form-control')        
//    ))
//    ->add('editeur', TextType::class, array(
//        'constraints' => array(new Assert\NotBlank(), 
//            new Assert\Length(array('min' => 2)),
//            new Assert\Length(array('max' => 30))
//        ),
//        'attr' => array('class'=>'form-control')        
//    ))
//    ->add('parution', DateType::class, array(
//        'constraints' => array(new Assert\NotBlank()),
//        'attr' => array('class'=>'form-control'),
//        'widget' => 'single_text',
//
//        // do not render as type="date", to avoid HTML5 date pickers
//        'html5' => true,
//
//        // add a class that can be selected in JavaScript
//        //        'attr' => ['class' => 'js-datepicker'],
//    ))
//    ->add('save', SubmitType::class, array(
//        'attr' => array('label' => 'Enregistrer', 'class'=>'btn btn-success'),
//    ))
//    ->add('reset', ResetType::class, array(
//        'attr' => array('label' => 'Effacer', 'class'=>'btn btn-default'),
//    ))
//    ->getForm();
    
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
                //return $app->abort('404', 'Resource not found');
                return $app->redirect($app['url_generator']->generate('albumotfound'));
            } else {
                // error_log(var_export($albums, true));
                foreach($albums[$id] as $key=>$value) {
                    $data[$key] = $app->escape($value);
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
            error_log('formulaire OK :)');
            error_log(var_export($data, true));
//
//    $user = new User();
//    $user->setUsername($data['username']);
//    $user->setEmail($data['email']);
//    $user->setPassword($this->encodePassword($user, $data['password']));
//
//    $em = $this->getDoctrine()->getManager();
//    $em->persist($user);
//    $em->flush();
//    
//            $session = $this->getRequest()->getSession();
//            $session->getFlashBag()->add('message', 'Article saved!');
            
            // redirection
            return $app->redirect($app['url_generator']->generate('albumbdregister'));
        } else {
            error_log('formulaire BAD :(');
            error_log(var_export($data, true));            
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
