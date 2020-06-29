<?php
class Booking
{
   var $id;
   var $ticketid;
   var $userid;
   var $status;
}

class UsersV2
{
   var $id;
   var $username;
   var $password;
   var $email;
   var $role;
}

class Ticket
{

   var $id;
   var $destfrom;
   var $destto;
   var $date;
   var $quantity;
   var $max;
   var $price;
}

class DbStatus
{
   var $status;
   var $error;
   var $lastinsertid;
}

function hashPassword($password)
{

   $cost = 10;

   $options = [
      'cost' => $cost,
   ];

   $passwordhash =  password_hash($password, PASSWORD_BCRYPT, $options);
   return $passwordhash;
}

class Database
{
   protected $dbhost;
   protected $dbuser;
   protected $dbpass;
   protected $dbname;
   protected $db;

   function __construct($dbhost, $dbuser, $dbpass, $dbname)
   {
      $this->dbhost = $dbhost;
      $this->dbuser = $dbuser;
      $this->dbpass = $dbpass;
      $this->dbname = $dbname;

      $db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
      $this->db = $db;
   }

   function beginTransaction()
   {
      try {
         $this->db->beginTransaction();
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   function commit()
   {
      try {
         $this->db->commit();
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   function rollback()
   {
      try {
         $this->db->rollback();
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   function close()
   {
      try {
         $this->db = null;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();
         return 0;
      }
   }

   // =============START EDIT FUNCTIONS HERE==============//

   function getUserIdByHash($password)
   {
      $sql = "SELECT id FROM users WHERE password = :password";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("password", $password);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      $user = null;

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new UsersV2();
            $user->id = $row['id'];
         }
      }

      return $user->id;
   }

   function getAllBooking()
   {
      try {
         $sql = "
            SELECT 
               bookings.*,
               users.username,
               users.email,
               tickets.destfrom,
               tickets.destto,
               tickets.date,
               tickets.quantity,
               tickets.max,
               tickets.price
            FROM
               bookings
                  LEFT JOIN
               users ON bookings.userid = users.id
                  LEFT JOIN
               tickets ON tickets.id = bookings.ticketid";

         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $object = new stdClass();
               $object->id = $row['id'];
               $object->ticketid = $row['ticketid'];
               $object->userid = $row['userid'];
               $object->status = $row['status'];
               $object->username = $row['username'];
               $object->email = $row['email'];
               $object->destfrom = $row['destfrom'];
               $object->destto = $row['destto'];
               $object->date = $row['date'];
               $object->quantity = $row['quantity'];
               $object->max = $row['max'];
               $object->price = $row['price'];

               array_push($data, $object);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function updateBooking($id)
   {
      try {
         $sql = "UPDATE bookings SET status = not status WHERE id = :id";
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->execute();

         $sql = "
            SELECT 
               bookings.*,
               users.username,
               users.email,
               tickets.destfrom,
               tickets.destto,
               tickets.date,
               tickets.quantity,
               tickets.max,
               tickets.price
            FROM
               bookings
                  LEFT JOIN
               users ON bookings.userid = users.id
                  LEFT JOIN
               tickets ON tickets.id = bookings.ticketid";

         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $object = new stdClass();
               $object->id = $row['id'];
               $object->ticketid = $row['ticketid'];
               $object->userid = $row['userid'];
               $object->status = $row['status'];
               $object->username = $row['username'];
               $object->email = $row['email'];
               $object->destfrom = $row['destfrom'];
               $object->destto = $row['destto'];
               $object->date = $row['date'];
               $object->quantity = $row['quantity'];
               $object->max = $row['max'];
               $object->price = $row['price'];

               array_push($data, $object);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         // die('ERROR: ' . $e->getMessage());
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   function getUserBooking($email)
   {
      try {
         $sql = "
            SELECT 
               bookings.*,
               users.username,
               users.email,
               tickets.destfrom,
               tickets.destto,
               tickets.date,
               tickets.quantity,
               tickets.max,
               tickets.price
            FROM
               bookings
                  LEFT JOIN
               users ON bookings.userid = users.id
                  LEFT JOIN
               tickets ON tickets.id = bookings.ticketid
            WHERE users.email = :email";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("email", $email);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $object = new stdClass();
               $object->id = $row['id'];
               $object->ticketid = $row['ticketid'];
               $object->userid = $row['userid'];
               $object->status = $row['status'];
               $object->username = $row['username'];
               $object->email = $row['email'];
               $object->destfrom = $row['destfrom'];
               $object->destto = $row['destto'];
               $object->date = $row['date'];
               $object->quantity = $row['quantity'];
               $object->max = $row['max'];
               $object->price = $row['price'];

               array_push($data, $object);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }


   function authenticateUser($email)
   {
      $sql = "SELECT id,username, password as passwordhash, email, role
              FROM users
              WHERE email = :email";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("email", $email);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      $user = null;

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new UsersV2();
            $user->id = $row['id'];
            $user->username = $row['username'];
            $user->passwordhash = $row['passwordhash'];
            $user->email = $row['email'];
            $user->role = $row['role'];
         }
      }

      return $user;
   }

   function createBooking($ticket_id, $user_id)
   {


      $sql = "SELECT * from tickets
                 WHERE id = :ticketid";

      $stmt = $this->db->prepare($sql);
      $stmt->bindParam("ticketid", $ticket_id);
      $stmt->execute();
      $row_count = $stmt->rowCount();

      if ($row_count) {
         while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ticket = new Ticket();
            $ticket->quantity = $row['quantity'];
            $ticket->max = $row['max'];
         }

         $quantity = $ticket->quantity;
         $max = $ticket->max;
         if ($quantity < $max) {

            $sql = "INSERT INTO bookings (ticketid, userid)
            VALUES (:ticketid, :userid)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("ticketid", $ticket_id);
            $stmt->bindParam("userid", $user_id);
            $stmt->execute();

            $new_quantity = $quantity + 1;

            $sql = "UPDATE tickets SET quantity = :quantity WHERE id = :ticketid";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam("quantity", $new_quantity);
            $stmt->bindParam("ticketid", $ticket_id);
            $stmt->execute();

            $status = "Success";
         } else {
            $status = "Full";
         }
      }
      return $status;
   }

   function getAllTicket()
   {
      try {
         $sql = "SELECT * FROM tickets";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();

         $index = 1;

         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

               $ticket = new Ticket();

               $ticket->index = $index;
               $index += 1;
               $ticket->id = $row['id'];
               $ticket->destfrom = $row['destfrom'];
               $ticket->destto = $row['destto'];
               $ticket->date = $row['date'];
               $ticket->quantity = $row['quantity'];
               $ticket->max = $row['max'];
               $ticket->price = $row['price'];

               array_push($data, $ticket);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function getEditTicket($id)
   {
      try {
         $sql = "SELECT * FROM tickets WHERE id = :id";
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         //$data = array();
         $ticket = new Ticket();

         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

               $ticket->id = $row['id'];
               $ticket->destfrom = $row['destfrom'];
               $ticket->destto = $row['destto'];
               $ticket->date = $row['date'];
               $ticket->quantity = $row['quantity'];
               $ticket->max = $row['max'];
               $ticket->price = $row['price'];

               //array_push($data, $ticket);
            }

            echo json_encode($ticket);
            exit;
         } else {
            echo json_encode($ticket);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function insertTicket($destfrom, $destto, $date, $max, $price)
   {

      try {

         $sql = "INSERT INTO tickets (destfrom, destto, date, max, price) 
                 VALUES (:destfrom, :destto, :date, :max, :price)";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("destfrom", $destfrom);
         $stmt->bindParam("destto", $destto);
         $stmt->bindParam("date", $date);
         $stmt->bindParam("max", $max);
         $stmt->bindParam("price", $price);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";
         $dbs->lastinsertid = $this->db->lastInsertId();

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   function updateTicket($id, $destfrom, $destto, $date, $max, $price)
   {

      $sql = "UPDATE tickets
               SET destfrom = :destfrom,
                  destto = :destto,
                  date = :date,
                  max = :max,
                  price = :price
               WHERE id = :id";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->bindParam("destfrom", $destfrom);
         $stmt->bindParam("destto", $destto);
         $stmt->bindParam("date", $date);
         $stmt->bindParam("max", $max);
         $stmt->bindParam("price", $price);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }

   function deleteTicket($id)
   {

      $dbstatus = new DbStatus();

      $sql = "DELETE 
              FROM tickets 
              WHERE id = :id";

      try {
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("id", $id);
         $stmt->execute();

         $dbstatus->status = true;
         $dbstatus->error = "none";
         return $dbstatus;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbstatus->status = false;
         $dbstatus->error = $errorMessage;
         return $dbstatus;
      }
   }

   function getUserProfile($email)
   {
      try {
         $sql = "SELECT * FROM users WHERE email = :email";
         $stmt = $this->db->prepare($sql);
         // $email= "test@gmail.com";
         $stmt->bindParam("email", $email);
         $stmt->execute();
         $user = new UsersV2();
         $row_count = $stmt->rowCount();
         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

               $user->id = $row['id'];
               $user->username = $row['username'];
               $user->email = $row['email'];
               $user->role = $row['role'];
               $user->password = "";

               // array_push($data, $user);
            }
            echo json_encode($user);
            exit;
            // return $user;

         } else {
            echo json_encode($user);
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function getAllUser()
   {
      try {
         $sql = "SELECT * FROM users";
         $stmt = $this->db->prepare($sql);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         $data = array();


         if ($row_count) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $user = new UsersV2();
               $user->id = $row['id'];
               $user->username = $row['username'];
               $user->email = $row['email'];
               $user->role = $row['role'];

               array_push($data, $user);
            }

            echo json_encode($data);
            exit;
         } else {
            echo json_encode($data);
            exit;
         }
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function updateProfile($id, $username)
   {
      try {
         $sql = "UPDATE users SET username = :username WHERE email = :email";
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("email", $id);
         $stmt->bindParam("username", $username);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";
         return $dbs;
      } catch (PDOException $e) {
         die('ERROR: ' . $e->getMessage());
      }
   }

   function insertUser($email, $clearpassword,$username)
   {

      //hash the password using one way md5 brcrypt hashing
      $passwordhash = hashPassword($clearpassword);
      try {

         $sql = "INSERT INTO users(email, password,username, role) 
                    VALUES (:email, :password, :username, 'member')";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("email", $email);
         $stmt->bindParam("password", $passwordhash);
         $stmt->bindParam("username", $username);
         $stmt->execute();

         $dbs = new DbStatus();
         $dbs->status = true;
         $dbs->error = "none";
         $dbs->lastinsertid = $this->db->lastInsertId();

         return $dbs;
      } catch (PDOException $e) {
         $errorMessage = $e->getMessage();

         $dbs = new DbStatus();
         $dbs->status = false;
         $dbs->error = $errorMessage;

         return $dbs;
      }
   }
   
}





   // =============EDIT FUNCTION END HERE==============//
