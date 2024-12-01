<?php

class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $this->view('users/index');
    }

    public function chart()
    {
        $users = $this->userModel->getAllUsers();

        $this->view('users/chart', ['users' => $users]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = $this->validateUserInput($_POST);

            if (!empty($errors)) {
                echo json_encode(['status' => 'error', 'errors' => $errors]);
                return;
            }

            $data = [
                'full_name' => $_POST['fullName'],
                'phone_number' => $_POST['phoneNumber'],
                'email' => $_POST['email'],
                'image' => $_FILES['image']
            ];

            $this->userModel->createUser($data);
            echo json_encode(['status' => 'success']);
        }
    }

    private function validateUserInput($data)
    {
        $errors = [];

        if (empty($data['fullName'])) {
            $errors['fullName'] = 'Full Name is required.';
        }
        if (empty($data['phoneNumber'])) {
            $errors['phoneNumber'] = 'Phone Number is required.';
        } elseif (!preg_match('/^\d{10}$/', $data['phoneNumber'])) {
            $errors['phoneNumber'] = 'Phone Number must be 10 digits.';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid Email format.';
        }

        return $errors;
    }

    public function listUsers()
    {
        $start = $_GET['start'];
        $length = $_GET['length'];
        $search = $_GET['search']['value'];

        $users = $this->userModel->getUsers($start, $length, $search);

        $totalRecords = $this->userModel->getTotalUsersCount();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user['id'],
                'fullName' => $user['full_name'],
                'phoneNumber' => $user['phone_number'],
                'email' => $user['email'],
                'image' => $this->userModel->getUserImage($user['id'])
            ];
        }

        echo json_encode([
            'draw' => $_GET['draw'],
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    public function getChartData()
    {
        $userId = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;

        $chartData = $this->userModel->getChartData($userId);

        echo json_encode($chartData);
    }
}
