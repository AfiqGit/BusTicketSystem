<?php
/*
   POST contacts DONE
   GET contacts => get al contacts using ownerlogin in token payload DONE
   GET contacts/{id, ownerlogin} DONE => ownerlogin for rolling no hacking prevention
   PUT contacts/{id} DONE 
   PUT contacts/status/{id} DONE => update status
   DELETE contacts/{id} DONE

   GET users/{login} get profile DONE
   PUT users/{login} profile update DONE
   PUT users/password/{login} reset password DONE
   */

ini_set("date.timezone", "Asia/Kuala_Lumpur");

header('Access-Control-Allow-Origin: *');

//*
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
   // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
   // you want to allow, and if so:
   header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
   header('Access-Control-Allow-Credentials: true');
   header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

   if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
      header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");

   if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
      header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

   exit(0);
}
//*/

include_once("database_class.php");

require_once 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

//load environment variable - jwt secret key
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

//jwt secret key in case dotenv not working in apache
//$jwtSecretKey = "jwt_secret_key";

use Slim\App;
use Slim\Middleware\TokenAuthentication;
use Firebase\JWT\JWT;

//functions /////////////////////////////////////////////start

function generateToken($role, $username, $email)
{

   //create JWT token
   $date = date_create();
   $jwtIAT = date_timestamp_get($date);
   $jwtExp = $jwtIAT + (180 * 60); //expire after 3 hours

   $jwtToken = array(
      "iss" => "rahsialah", //client key
      "iat" => $jwtIAT, //issued at time
      "exp" => $jwtExp, //expire
      "role" => $role,
      "username" => $username,
      "email" => $email
   );
   $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));
   return $token;
}

// function generateTokenV2($role, $username, $email,$id) {      

//    //create JWT token
//    $date = date_create();
//    $jwtIAT = date_timestamp_get($date);
//    $jwtExp = $jwtIAT + (180 * 60); //expire after 3 hours

//    $jwtToken = array(
//       "iss" => "busticket", //client key
//       "iat" => $jwtIAT, //issued at time
//       "exp" => $jwtExp, //expire
//       "role" => $role,
//       "username" => $username,
//       "email" => $email,
//       "id"=> $id
//    );
//    $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));
//    return $token;
// }

function getDatabase()
{
   $dbhost = "localhost";
   $dbuser = "root";
   $dbpass = "";
   $dbname = "busticket";

   $db = new Database($dbhost, $dbuser, $dbpass, $dbname);
   return $db;
}

function getLoginFromTokenPayload($request, $response)
{
   $token_array = $request->getHeader('HTTP_AUTHORIZATION');
   $token = substr($token_array[0], 7);

   //decode the token
   try {
      $tokenDecoded = JWT::decode(
         $token,
         getenv('JWT_SECRET'),
         array('HS256')
      );

      //in case dotenv not working
      /*
         $tokenDecoded = JWT::decode(
            $token, 
            $GLOBALS['jwtSecretKey'], 
            array('HS256')
         );
         */
   } catch (Exception $e) {
      $data = array(
         "message" => "Token invalid"
      );

      return $response->withJson($data, 401)
         ->withHeader('Content-tye', 'application/json');
   }

   // return $tokenDecoded->login;
   return $tokenDecoded;
}
//functions /////////////////////////////////////////////ends

$config = [
   'settings' => [
      'displayErrorDetails' => true
   ]
];

$app = new App($config);

/**
 * Token authentication middleware logic
 */
$authenticator = function ($request, TokenAuthentication $tokenAuth) {

   /**
    * Try find authorization token via header, parameters, cookie or attribute
    * If token not found, return response with status 401 (unauthorized)
    */
   $token = $tokenAuth->findToken($request); //from header

   try {
      $tokenDecoded = JWT::decode($token, getenv('JWT_SECRET'), array('HS256'));

      //in case dotenv not working
      // $tokenDecoded = JWT::decode($token, $GLOBALS['jwtSecretKey'], array('HS256'));
   } catch (Exception $e) {
      throw new \app\UnauthorizedException('Invalid Token');
   }
};

/**
 * Add and manage token authentication middleware => $authenticator
 * passthrough means, no token needed, a public/guest route
 */
$app->add(new TokenAuthentication([
   'path' => '/', //secure route - need token
   'passthrough' => [ //public route, no token needed
      '/ping',
      '/token',
      '/auth',
      '/hello',
      '/calc',
      '/registration'
   ],
   'authenticator' => $authenticator
]));

// ==============EDIT START HERE===============

$app->get('/booking', function ($request, $response, $args) {
   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "admin") {
      $db = getDatabase();
      $data = $db->getAllBooking();
      $db->close();
      // return $data;
      return $response->withJson($data, 200)
         ->withHeader('Content-type', 'application/json');
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});

$app->put('/booking/flipstatus/[{id}]', function ($request, $response, $args) {
   $id = $args['id'];
   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "admin") {
      $db = getDatabase();
      $data = $db->updateBooking($id);
      $db->close();
      return $response->withJson($data, 200)
         ->withHeader('Content-type', 'application/json');
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});

$app->get('/mybooking', function ($request, $response, $args) {
   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "member") {
      $db = getDatabase();
      $data = $db->getUserBooking($user->email);
      $db->close();
      // return $data;
      return $response->withJson($data, 200)
         ->withHeader('Content-type', 'application/json');
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});

/**
 * Public route /auth for creds authentication / login process
 */
$app->post('/auth', function ($request, $response) {

   //extract form data - email and password
   $json = json_decode($request->getBody());
   $email = $json->email;
   $clearpassword = $json->password;

   //do db authentication
   $db = getDatabase();
   $data = $db->authenticateUser($email);
   $db->close();

   //status -1 -> user not found
   //status 0 -> wrong password
   //status 1 -> login success

   $returndata = array();

   //user not found
   if ($data === NULL) {
      $returndata = array(
         "loginStatus" => false,
         "errorMessage" => "Username/password is incorrect!"
      );
   } else { //user found

      if (password_verify($clearpassword, $data->passwordhash)) {

         //create JWT token
         $date = date_create();
         $jwtIAT = date_timestamp_get($date);
         $jwtExp = $jwtIAT + (60 * 60 * 12); //expire after 12 hours

         $jwtToken = array(
            "iss" => "mycontacts.net", //token issuer
            "iat" => $jwtIAT, //issued at time
            "exp" => $jwtExp, //expire
            "role" => $data->role,
            "email" => $data->email,
            "username" => $data->username,
            "password" => $data->passwordhash
         );
         $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));

         $returndata = array(
            "loginStatus" => true,
            "token" => $token
         );
      } else {

         $returndata = array(
            "loginStatus" => false,
            "errorMessage" => "Username/password is incorrect!"
         );
      }
   }

   return $response->withJson($returndata, 200)
      ->withHeader('Content-type', 'application/json');
});


$app->post('/createBooking', function ($request, $response) {
   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "member") {
      $json = json_decode($request->getBody());

      $ticket_id = $json->ticket_id;
      $user = getLoginFromTokenPayload($request, $response);
      $password = $user->password;

      $db = getDatabase();
      $user_id = $db->getUserIdByHash($password);
      $status = $db->createBooking($ticket_id, $user_id);

      $returndata = array(
         "bookingStatus" => $status,
      );

      return $response->withJson($returndata, 200)
         ->withHeader('Content-type', 'application/json');
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});


$app->get('/ticket', function ($request, $response) {

   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "admin") {
      $db = getDatabase();
      $data = $db->getAllTicket();
      $db->close();
      return $data;
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});

$app->get('/ticketmember', function ($request, $response) {

   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "member") {
      $db = getDatabase();
      $data = $db->getAllTicket();
      $db->close();
      return $data;
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});

$app->get('/ticket/[{id}]', function ($request, $response, $args) {

   $id = $args['id'];

   $db = getDatabase();
   $data = $db->getEditTicket($id);
   $db->close();
   return $data;
});

$app->post('/ticket', function ($request, $response) {

   $json = json_decode($request->getBody());

   $destfrom   = $json->destfrom;
   $destto     = $json->destto;
   $date       = $json->date;
   $max        = $json->max;
   $price      = $json->price;

   $db = getDatabase();
   $dbs = $db->insertTicket($destfrom, $destto, $date, $max, $price);
   $db->close();

   $data = array(
      "insertStatus" => $dbs->status,
      "errorMessage" => $dbs->error
   );


   return $response->withJson($data, 200)
      ->withHeader('Content-type', 'application/json');
});

$app->put('/ticket/[{id}]', function ($request, $response, $args) {

   $id = $args['id'];

   $json = json_decode($request->getBody());
   $destfrom   = $json->destfrom;
   $destto     = $json->destto;
   $date       = $json->date;
   $max        = $json->max;
   $price      = $json->price;

   $db = getDatabase();
   $dbs = $db->updateTicket($id, $destfrom, $destto, $date, $max, $price);
   $db->close();

   $data = array(
      "updateStatus" => $dbs->status,
      "errorMessage" => $dbs->error
   );

   return $response->withJson($data, 200)
      ->withHeader('Content-type', 'application/json');
});

$app->delete('/ticket/[{id}]', function ($request, $response, $args) {

   $id = $args['id'];

   $db = getDatabase();
   $dbs = $db->deleteTicket($id);
   $db->close();

   $data = array(
      "deleteStatus" => $dbs->status,
      "errorMessage" => $dbs->error
   );

   return $response->withJson($data, 200)
      ->withHeader('Content-type', 'application/json');
});

$app->get('/profile', function ($request, $response) {

   $user = getLoginFromTokenPayload($request, $response);
   $user_email = $user->email;
   $db = getDatabase();
   $data = $db->getUserProfile($user_email);
   $db->close();
   return $data;
});

$app->get('/userlist', function ($request, $response) {

   $user = getLoginFromTokenPayload($request, $response);
   if ($user->role == "admin") {
      $db = getDatabase();
      $data = $db->getAllUser();
      $db->close();
      return $response->withJson($data, 200)
         ->withHeader('Content-type', 'application/json');
   } else {

      $msj = array(
         "message" => "User have no permission to access this API."
      );
      return $response->withJson($msj, 403)
         ->withHeader('Content-type', 'application/json');
   }
});

$app->put('/profile', function ($request, $response) {
   $user = getLoginFromTokenPayload($request, $response);
   $id = $user->email;
   $json = json_decode($request->getBody());
   $username = $json->username;
   $db = getDatabase();
   $dbs = $db->updateProfile($id, $username);
   $db->close();
   $data = array(
      "insertStatus" => $dbs->status,
      "errorMessage" => $dbs->error
   );
   return $response->withJson($data, 200)
      ->withHeader('Content-type', 'application/json');
});

$app->post('/registration', function ($request, $response) {

   $json = json_decode($request->getBody());
   $email = $json->email;
   $clearpassword = $json->password;
   $username = $json->username;

   //insert user
   $db = getDatabase();
   $dbs = $db->insertUser($email, $clearpassword, $username);
   $db->close();

   $data = array(
      "registrationStatus" => $dbs->status,
      "errorMessage" => $dbs->error
   );

   return $response->withJson($data, 200)
      ->withHeader('Content-type', 'application/json');
});

// ==============EDIT END HERE===============

$app->run();
