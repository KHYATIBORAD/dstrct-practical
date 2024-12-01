<?php

class UserModel extends Model
{
    public function createUser($data)
    {
        $stmt = $this->connection->prepare("INSERT INTO users (full_name, phone_number, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data['full_name'], $data['phone_number'], $data['email']);
        $result = $stmt->execute();

        if ($result) {
            $lastInsertId = $this->connection->insert_id;
        } else {
            return false;
        }

        if (isset($_FILES['image']['name'][0]) && !empty($_FILES['image']['name'][0])) {
            foreach ($_FILES['image']['name'] as $key => $name) {
                if (empty($_FILES['image']['tmp_name'][$key])) {
                    continue;
                }
                $this->uploadImageToBunnyStorage($_FILES['image'], $key, $lastInsertId);
            }
        }

        return true;
    }

    public function uploadImageToBunnyStorage($image, $key, $userId)
    {
        $imageName = $image['name'][$key];
        $imagePath = $image['tmp_name'][$key];

        $url = "https://storage.bunnycdn.com/dstrct-practical/" . $imageName;

        // Read the file contents
        $fileContent = file_get_contents($imagePath);
        if ($fileContent === false) {
            die("Error: Unable to read the file.");
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'PUT',
                'header' => [
                    "AccessKey: 61074ddb-99bb-4dbd-9c2ee018f531-8908-400b",
                    "Content-Type: " . mime_content_type($imagePath),
                    "Content-Length: " . strlen($fileContent),
                ],
                'content' => $fileContent,
            ],
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            echo "Error during file upload.";
        } else {
            $stmt = $this->connection->prepare("INSERT INTO users_image (user_id, image_name) VALUES (?, ?)");
            $stmt->bind_param("ss", $userId, $imageName);
            $stmt->execute();
        }
    }

    public function getUsers($start, $length, $search)
    {
        $sql = "SELECT * FROM users WHERE 1";

        if ($search) {
            $sql .= " AND (full_name LIKE ? OR phone_number LIKE ? OR email LIKE ?)";
        }

        $sql .= " LIMIT ?, ?";

        $stmt = $this->connection->prepare($sql);

        if ($search) {
            $searchTerm = "%" . $search . "%";
            $stmt->bind_param("ssssi", $searchTerm, $searchTerm, $searchTerm, $start, $length);
        } else {
            $stmt->bind_param("ii", $start, $length);
        }

        $stmt->execute();

        $result = $stmt->get_result();

        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }

    public function getTotalUsersCount()
    {
        $sql = "SELECT COUNT(*) AS total FROM users";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        return $row['total'];
    }

    public function getUserImage($userId)
    {
        $sql = "SELECT * FROM users_image WHERE user_id = " . $userId;

        $stmt = $this->connection->prepare($sql);

        $stmt->execute();

        $result = $stmt->get_result();

        $usersImage = [];
        while ($row = $result->fetch_assoc()) {
            $usersImage[] = $this->sign_bcdn_url(
                'https://dstrct.b-cdn.net/' . $row['image_name'],
                '56772d24-3cef-4a9a-8189-849057903283',
                360000,
                null,
                false,
                '/');
        }

        return $usersImage;
    }

    public function sign_bcdn_url($url, $securityKey, $expiration_time = 3600, $user_ip = NULL, $is_directory_token = false, $path_allowed = NULL, $countries_allowed = NULL, $countries_blocked = NULL, $referers_allowed = NULL)
    {
        if (!is_null($countries_allowed)) {
            $url .= (parse_url($url, PHP_URL_QUERY) == "") ? "?" : "&";
            $url .= "token_countries={$countries_allowed}";
        }
        if (!is_null($countries_blocked)) {
            $url .= (parse_url($url, PHP_URL_QUERY) == "") ? "?" : "&";
            $url .= "token_countries_blocked={$countries_blocked}";
        }
        if (!is_null($referers_allowed)) {
            $url .= (parse_url($url, PHP_URL_QUERY) == "") ? "?" : "&";
            $url .= "token_referer={$referers_allowed}";
        }

        $url_scheme = parse_url($url, PHP_URL_SCHEME);
        $url_host = parse_url($url, PHP_URL_HOST);
        $url_path = parse_url($url, PHP_URL_PATH);
        $url_query = parse_url($url, PHP_URL_QUERY);


        $parameters = array();
        // parse_str($url_query, $parameters);

        // Check if the path is specified and ovewrite the default
        $signature_path = $url_path;

        if (!is_null($path_allowed)) {
            $signature_path = $path_allowed;
            $parameters["token_path"] = $signature_path;
        }

        // Expiration time
        $expires = time() + $expiration_time;

        // Construct the parameter data
        ksort($parameters); // Sort alphabetically, very important
        $parameter_data = "";
        $parameter_data_url = "";
        if (sizeof($parameters) > 0) {
            foreach ($parameters as $key => $value) {
                if (strlen($parameter_data) > 0)
                    $parameter_data .= "&";

                $parameter_data_url .= "&";

                $parameter_data .= "{$key}=" . $value;
                $parameter_data_url .= "{$key}=" . urlencode($value); // URL encode everything but slashes for the URL data
            }
        }

        // Generate the toke
        $hashableBase = $securityKey . $signature_path . $expires;

        // If using IP validation
        if (!is_null($user_ip)) {
            $hashableBase .= $user_ip;
        }

        $hashableBase .= $parameter_data;

        // Generate the token
        $token = hash('sha256', $hashableBase, true);
        $token = base64_encode($token);
        $token = strtr($token, '+/', '-_');
        $token = str_replace('=', '', $token);

        if ($is_directory_token) {
            return "{$url_scheme}://{$url_host}/bcdn_token={$token}&expires={$expires}{$parameter_data_url}{$url_path}";
        } else {
            return "{$url_scheme}://{$url_host}{$url_path}?token={$token}{$parameter_data_url}&expires={$expires}";
        }
    }

    public function getChartData($userId)
    {
        $sql = "SELECT us.score, us.score_date FROM user_scores us";
        if ($userId) {
            $sql .= " WHERE us.user_id = $userId";
        }
        $sql .= " ORDER BY us.score_date ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        return $data;
    }
}
