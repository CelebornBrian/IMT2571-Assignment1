<?php
include_once("IModel.php");
include_once("Book.php");


/** The Model is the class holding data about a collection of books. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
class DBModel implements IModel
{        
    /**
      * The PDO object for interfacing the database
      *
      */
    protected $db = null;  
    
    /**
	 * @throws PDOException
     */
    public function __construct($db = null)  
    {
        if ($db) 
        {
            $this->db = $db;
        }
        else
        {
            // Create PDO connection            
            try {
                $this->db = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4',
                'root', '',
                // Set server in exception mode
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)); 
                //Catch error and throw
            } catch(PDOException $ex) {
            throw $Ex;
            }  
        }
    }
    
    /** Function returning the complete list of books in the collection. Books are
     * returned in order of id.
     * @return Book[] An array of book objects indexed and ordered by their id.
	 * @throws PDOException
     */
    public function getBookList()
    {
        $booklist = array();
        try{    //Get each tuple from the database and push to array
            foreach($this->db->query("SELECT * FROM book") as $row) {
                array_push($booklist, new Book($row['title'], $row['author'], $row['description'], $row['id']));
            }
        } catch (Exception $Ex) {
            throw $Ex;
        }
        return $booklist;
    }
    
    /** Function retrieving information about a given book in the collection.
     * @param integer $id the id of the book to be retrieved
     * @return Book|null The book matching the $id exists in the collection; null otherwise.
	 * @throws PDOException
     */
    public function getBookById($id)
    {
        //Check for valid ID:
        if(!is_numeric($id)) {
            throw new Exception("Invalid ID");
        }
        //Get the tuple from the database based on the valid ID that was given
        try {
            $row = $this->db->query("SELECT * FROM book WHERE id=$id")->fetch(PDO::FETCH_ASSOC);            
        } catch(PDOException $Ex) {
            throw $Ex;
        }
        //If there was returned a book, return that, else return NULL
        if($row){
            $book = new Book($row['title'], $row['author'], $row['description'], $row['id']);
            return $book;
        }else{
            return NULL;
        }
    }
    
    /** Adds a new book to the collection.
     * @param $book Book The book to be added - the id of the book will be set after successful insertion.
	 * @throws PDOException
     */
    public function addBook($book)
    {
        //Check for values in title and author
        if (!($book->title && $book->author))
            throw new PDOException("No title and/or author in addBook");
        //Prepare statement and bind values for the book to be added to the database
        try {
            $stmt = $this->db->prepare('INSERT INTO book (title, author, description)'
            . 'VALUES (:title, :author, :description)');
            $stmt->bindValue(':title', $book->title);
            $stmt->bindValue(':author', $book->author);
            $stmt->bindValue(':description', $book->description);
            $stmt->execute();
            //Give the book the ID it got when inserted into the database
            $book->id = $this->db->lastInsertId();    
            } catch(PDOException $Ex) {
            throw $Ex;
        }        
    }

    /** Modifies data related to a book in the collection.
     * @param $book Book The book data to be kept.
     * @todo Implement function using PDO and a real database.
     */
    public function modifyBook($book)
    {
        //Check for values in title and author
        if (!($book->title && $book->author))
            throw new PDOException("No title and/or author in modifyBook\n");
        //Prepare statement for updating a book
        try {
            $stmt = $this->db->prepare('UPDATE book SET title=:title, author=:author, description=:description 
            WHERE id=' . $book->id);
            $stmt->bindValue(':title', $book->title);
            $stmt->bindValue(':author', $book->author);
            $stmt->bindValue(':description', $book->description);
            $stmt->execute();
    
            } catch(PDOException $Ex) {
            throw $Ex;
        }
    }

    /** Deletes data related to a book from the collection.
     * @param $id integer The id of the book that should be removed from the collection.
     */
    public function deleteBook($id)
    {
        if(is_numeric($id))
            $this->db->exec('DELETE FROM book WHERE ID=' . $id);            
        else {
            throw new Exception("Cannot delete unnumeric ID");
        }
    }
}

?>