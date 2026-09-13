<?php

class Business {
    private $conn;
    private $table = "businesses";

    public function __construct($db) {
        $this->conn = $db;
    }




    public function getByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function createOrUpdate($userId, $data, $logoFile = null) {
        $existing = $this->getByUserId($userId);

        if ($existing) { 
            $logoQuery = $logoFile ? ", logo = :logo" : "";
            $query = "UPDATE " . $this->table . " 
                      SET business_name = :name, category = :cat, address = :address, 
                          phone = :phone, description = :desc, opening_hours = :hours" . 
                      $logoQuery . " WHERE user_id = :user_id";
        } else {
            $query = "INSERT INTO " . $this->table . " 
                      (user_id, business_name, category, address, phone, description, opening_hours" . 
                      ($logoFile ? ", logo" : "") . ") 
                      VALUES (:user_id, :name, :cat, :address, :phone, :desc, :hours" . 
                      ($logoFile ? ", :logo" : "") . ")";
        }



        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':name', $data['business_name']);
        $stmt->bindParam(':cat', $data['category']);   
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':hours', $data['opening_hours']);



        if ($logoFile || !$existing) {
            $logoToSave = $logoFile ?? 'default_logo.png';
            $stmt->bindParam(':logo', $logoToSave);
        }



     return $stmt->execute();
    }
}