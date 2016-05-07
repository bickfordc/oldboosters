<!DOCTYPE html>
<html>
  <head>
    <title>Setting up database</title>
  </head>
  <body>

    <h3>Setting up...</h3>

<?php 
  require_once 'functions.php';

  $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS members 
      (usr varchar(32),
       pass varchar(32));
EOF;
  
  postgres_query($sql);
  
  
    $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS reset_requests
      (code char(8) PRIMARY KEY,
       usr varchar(32) NOT NULL,
       expiration timestamp NOT NULL DEFAULT NOW() + INTERVAL '20 minutes');    
EOF;
  
    postgres_query($sql);
    
    $sql =<<<EOF
    CREATE OR REPLACE FUNCTION delete_old_reset_requests() RETURNS trigger
      LANGUAGE plpgsql
      AS $$
      BEGIN
        DELETE FROM reset_requests WHERE expiration < NOW();
        RETURN NEW;
      END;
      $$;
EOF;
    
    postgres_query($sql);
    
    $sql =<<<EOF
    CREATE TRIGGER delete_old_reset_requests_trigger 
      AFTER INSERT ON reset_requests
      EXECUTE PROCEDURE delete_old_reset_requests();
EOF;
    
    postgres_query($sql);
    
    $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS students
      (id SERIAL PRIMARY KEY,
       first varchar(32),
       last varchar(32) NOT NULL,
       email varchar(80),
       active boolean DEFAULT TRUE
      );
EOF;
    
    postgres_query($sql);
    
    $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS cards
      (id varchar(20) PRIMARY KEY,
       sold boolean DEFAULT FALSE,
       card_holder varchar(80),
       notes varchar(80),
       active boolean DEFAULT TRUE,
       donor_code char(2) 
      );
       
EOF;
    
    postgres_query($sql);
            
    $sql =<<<EOF
    CREATE TABLE IF NOT EXISTS student_cards
      (student integer REFERENCES students (id),
       card varchar(20) REFERENCES cards (id),
       PRIMARY KEY (student, card)
      );
            
EOF;
    
    postgres_query($sql);
    
    
  function postgres_query($sql) {
    $ret = pg_query($sql);
    if(!$ret){
        echo pg_last_error($db);
    } else {
      echo "$sql<br>Success.<br>";
    }
  }
  
?>

    <br>...done.
  </body>
</html>
