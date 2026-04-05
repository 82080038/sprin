<?php
/**
 * Personil API
 *
 * @package SPRIN
 * @author Development Team
 * @since 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../core/config.php';

/**
 * Personil API Class
 */
class PersonilAPI {

    private $pdo;

    /**
     * Constructor
     */
    public function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';unix_socket=' . DB_SOCKET,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get all personil
     */
    public function getAllPersonil(): array {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM personil ORDER BY nama_lengkap');
            $stmt->execute();

            $personil = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'status' => 'success',
                'data' => $personil,
                'count' => count($personil)
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get personil by ID
     */
    public function getPersonilById(int $id): array {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM personil WHERE id = ?');
            $stmt->execute([$id]);

            $personil = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$personil) {
                return [
                    'status' => 'error',
                    'message' => 'Personil not found'
                ];
            }

            return [
                'status' => 'success',
                'data' => $personil
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create personil
     */
    public function createPersonil(array $data): array {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO personil (nama_lengkap, nrp, id_pangkat, id_jabatan, id_bagian, JK, status_ket)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $data['nama_lengkap'],
                $data['nrp'],
                $data['id_pangkat'],
                $data['id_jabatan'],
                $data['id_bagian'],
                $data['JK'] ?? 'L',
                $data['status_ket'] ?? 'Aktif'
            ]);

            return [
                'status' => 'success',
                'message' => 'Personil created successfully',
                'id' => $this->pdo->lastInsertId()
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update personil
     */
    public function updatePersonil(int $id, array $data): array {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE personil SET
                    nama_lengkap = ?, nrp = ?, id_pangkat = ?, id_jabatan = ?,
                    id_bagian = ?, JK = ?, status_ket = ?
                WHERE id = ?
            ');

            $stmt->execute([
                $data['nama_lengkap'],
                $data['nrp'],
                $data['id_pangkat'],
                $data['id_jabatan'],
                $data['id_bagian'],
                $data['JK'] ?? 'L',
                $data['status_ket'] ?? 'Aktif',
                $id
            ]);

            return [
                'status' => 'success',
                'message' => 'Personil updated successfully'
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete personil
     */
    public function deletePersonil(int $id): array {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM personil WHERE id = ?');
            $stmt->execute([$id]);

            return [
                'status' => 'success',
                'message' => 'Personil deleted successfully'
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}

// Handle API requests
if (basename($_SERVER['PHP_SELF']) === 'personil_api.php') {
    header('Content-Type: application/json');

    $api = new PersonilAPI();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $api->getPersonilById((int)$_GET['id']);
            } else {
                $result = $api->getAllPersonil();
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $api->createPersonil($data);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $api->updatePersonil((int)$_GET['id'], $data);
            break;

        case 'DELETE':
            $result = $api->deletePersonil((int)$_GET['id']);
            break;

        default:
            $result = ['status' => 'error', 'message' => 'Method not allowed'];
    }

    echo json_encode($result);
}
?>
