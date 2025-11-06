<?php
require_once __DIR__ . '/../config/database.php';

class Book {
    private $conn;
    private $table = 'books';

    public $id;
    public $isbn;
    public $title;
    public $author;
    public $publisher;
    public $published_year;
    public $category_id;
    public $available_quantity;
    public $description;
    public $cover_image;
    public $location;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAll($limit = null, $offset = 0) {
        $query = "SELECT b.*, c.name AS category_name
                  FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.id
                  ORDER BY b.created_at DESC";
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->conn->prepare($query);
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT b.*, c.name AS category_name
                  FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.id
                  WHERE b.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create() {
        // Normalize status to valid values
        $allowedStatus = ['available','unavailable'];
        if (!in_array($this->status, $allowedStatus, true)) {
            $this->status = 'available';
        }
        $statusValue = ($this->status === 'unavailable' || $this->status === '0' || $this->status === 0) ? 0 : 1;
        $query = "INSERT INTO {$this->table}
                  (isbn, title, author, publisher, published_year, category_id, available_quantity, description, cover_image, location, status)
                  VALUES
                  (:isbn, :title, :author, :publisher, :published_year, :category_id, :available_quantity, :description, :cover_image, :location, :status)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':isbn', $this->isbn);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':publisher', $this->publisher);
        $stmt->bindParam(':published_year', $this->published_year);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':available_quantity', $this->available_quantity);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cover_image', $this->cover_image);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindValue(':status', (int)$statusValue, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function update() {
        // Normalize status to valid values
        $allowedStatus = ['available','unavailable'];
        if (!in_array($this->status, $allowedStatus, true)) {
            $this->status = 'available';
        }
        $statusValue = ($this->status === 'unavailable' || $this->status === '0' || $this->status === 0) ? 0 : 1;
        $query = "UPDATE {$this->table}
                  SET
                    isbn = :isbn,
                    title = :title,
                    author = :author,
                    publisher = :publisher,
                    published_year = :published_year,
                    category_id = :category_id,
                    available_quantity = :available_quantity,
                    description = :description,
                    cover_image = :cover_image,
                    location = :location,
                    status = :status
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':isbn', $this->isbn);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':author', $this->author);
        $stmt->bindParam(':publisher', $this->publisher);
        $stmt->bindParam(':published_year', $this->published_year);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':available_quantity', $this->available_quantity);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':cover_image', $this->cover_image);
        $stmt->bindParam(':location', $this->location);
        $stmt->bindValue(':status', (int)$statusValue, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function search($keyword, $category = null, $limit = null, $offset = 0) {
        $query = "SELECT b.*, c.name AS category_name
                  FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.id
                  WHERE (b.title LIKE :kw OR b.author LIKE :kw OR b.isbn LIKE :kw)";
        if (!empty($category)) {
            $query .= " AND b.category_id = :category";
        }
        $query .= " ORDER BY b.title ASC";
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        $stmt = $this->conn->prepare($query);
        $kw = "%" . $keyword . "%";
        $stmt->bindValue(':kw', $kw);
        if (!empty($category)) {
            $stmt->bindValue(':category', $category);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSearchCount($keyword, $category = null) {
        $query = "SELECT COUNT(*) AS total FROM {$this->table} b WHERE (b.title LIKE :kw OR b.author LIKE :kw OR b.isbn LIKE :kw)";
        if (!empty($category)) {
            $query .= " AND b.category_id = :category";
        }
        $stmt = $this->conn->prepare($query);
        $kw = "%" . $keyword . "%";
        $stmt->bindValue(':kw', $kw);
        if (!empty($category)) {
            $stmt->bindValue(':category', $category);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row['total'] : 0;
    }

    public function getTotalCount() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM {$this->table}");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (int)$row['total'] : 0;
    }

    public function getByCategory($categoryId, $limit = null, $excludeId = null) {
        $query = "SELECT b.*, c.name AS category_name
                  FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.id
                  WHERE b.category_id = :category_id";
        if (!empty($excludeId)) {
            $query .= " AND b.id != :exclude_id";
        }
        $query .= " ORDER BY b.created_at DESC";
        if ($limit !== null) {
            $query .= " LIMIT :limit";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':category_id', $categoryId);
        if (!empty($excludeId)) {
            $stmt->bindValue(':exclude_id', $excludeId);
        }
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPopular($limit = 6) {
        $query = "SELECT b.*, c.name AS category_name, COUNT(br.id) AS borrow_count
                  FROM {$this->table} b
                  LEFT JOIN categories c ON b.category_id = c.id
                  LEFT JOIN borrows br ON b.id = br.book_id
                  GROUP BY b.id
                  ORDER BY borrow_count DESC, b.created_at DESC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>