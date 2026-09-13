<?php
require_once "models/business.php";

class businessController{
    private $db;
    private $businessModel;


    public function __construct($database){
        $this->db = $database->getConnection();
        $this->businessModel = new business($this->db);

    }

    public function dashboard(){
        $userId=$_SESSION['user_id'];
        $business = $this->businessModel->getByUserId($userId);

    $stats=[
        'total_products' => 0,
        'total_orders' => 0.00,
        'completed_orders' => 0
    ];
    require_once "views/dashboard.php";

}

public function profile(){
    $userId=$_SESSION['user_id'];
    $message ="";
    $error="";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $logoName = null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
       $fileTmp= $_FILES['logo']['tmp_name'];
       $fileName = time() . '_' . basename($_FILES['logo']['name']);
    $targetDir = "uploads/businesses/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);

       }

       $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
       $text= strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

       if (in_array($text, $allowed)){
        if (move_uploaded_file($fileTmp, $targetDir . $fileName)){
            $logoName = $fileName;
        } else {
            $error = "Failed to upload the image.";
        }
       } else {
        $error = "Invalid file type. Only JPG, JPEG, PNG, GIF, and WEBP are allowed.";
       }
    }

    if (empty($error)){
        $saved = $this->businessModel->createOrUpdate($userId, $_POST, $logoName);
    if ($saved){
        $message = "Profile updated successfully.";
    } else {
        $error = "Could not update profile. Please try again.";
    }
    }
    
    
    
        }

        $business = $this->businessModel->getByUserId($userId);
        require_once "views/business/profile.php";
}
} 
