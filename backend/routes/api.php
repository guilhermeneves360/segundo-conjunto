<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/TripController.php';
require_once __DIR__ . '/../controllers/ExpenseController.php';
require_once __DIR__ . '/../controllers/ItineraryController.php';
require_once __DIR__ . '/../controllers/ReservationController.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../services/WeatherService.php';
require_once __DIR__ . '/../services/ExportService.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^.*(?:index\.php|api\.php)/?#', '', $uri);
$path = preg_replace('#/+$#', '', $path);
$segments = $path === '' ? [] : explode('/', trim($path, '/'));
$body = json_decode(file_get_contents('php://input'), true) ?? [];

$authController = new AuthController($pdo);
$tripController = new TripController($pdo);
$expenseController = new ExpenseController($pdo);
$itineraryController = new ItineraryController($pdo);
$reservationController = new ReservationController($pdo);
$adminController = new AdminController($pdo);

function getAuthUser()
{
    return AuthMiddleware::authenticate();
}

if (($segments[0] ?? '') === 'api') {
    array_shift($segments);
}

if (!$segments) {
    Response::error('Endpoint não encontrado.', 404);
}

$resource = $segments[0] ?? null;
$id = isset($segments[1]) ? (int)$segments[1] : null;

switch ("$method $resource") {
    case 'POST auth':
        if (($segments[1] ?? '') === 'register') {
            $authController->register($body);
        }
        if (($segments[1] ?? '') === 'login') {
            $authController->login($body);
        }
        if (($segments[1] ?? '') === 'forgot-password') {
            $authController->forgotPassword($body);
        }
        if (($segments[1] ?? '') === 'reset-password') {
            $authController->resetPassword($body);
        }
        Response::error('Rota auth inválida.', 404);
        break;
    case 'GET trips':
        $user = getAuthUser();
        $tripController->index($user);
        break;
    case 'GET dashboard':
        $user = getAuthUser();
        $trips = $user['type'] === 'admin' ? $tripController->exportAll() : (new Trip($pdo))->getByUser($user['sub']);
        $tripIds = array_column($trips, 'id');
        $totalBudget = array_sum(array_map(fn($trip) => (float)$trip['budget'], $trips));
        $totalExpenses = 0;
        $reservations = 0;
        $expenseModel = new Expense($pdo);
        $reservationModel = new Reservation($pdo);
        foreach ($tripIds as $tripId) {
            $totalExpenses += (float)$expenseModel->getTotalByTrip((int)$tripId);
            $reservations += count($reservationModel->getByTrip((int)$tripId));
        }
        Response::json([
            'success' => true,
            'stats' => [
                'trips' => count($trips),
                'budget' => $totalBudget,
                'expenses' => $totalExpenses,
                'reservations' => $reservations
            ],
            'trips' => array_slice($trips, 0, 5)
        ]);
        break;
    case 'POST trips':
        $user = getAuthUser();
        $tripController->store($body, $user);
        break;
    case 'PUT trips':
        $user = getAuthUser();
        $tripController->update($id, $body, $user);
        break;
    case 'DELETE trips':
        $user = getAuthUser();
        $tripController->destroy($id, $user);
        break;
    case 'POST expenses':
        $user = getAuthUser();
        $expenseController->store($body, $user);
        break;
    case 'GET expenses':
        $user = getAuthUser();
        if (!$id) {
            Response::error('ID da viagem é obrigatório.', 422);
        }
        $expenseController->index($id, $user);
        break;
    case 'DELETE expenses':
        $user = getAuthUser();
        $expenseController->destroy($id, $user);
        break;
    case 'POST itinerary':
        $user = getAuthUser();
        $itineraryController->store($body, $user);
        break;
    case 'GET itinerary':
        $user = getAuthUser();
        if (!$id) {
            Response::error('ID da viagem é obrigatório.', 422);
        }
        $itineraryController->index($id, $user);
        break;
    case 'POST reservations':
        $user = getAuthUser();
        $reservationController->store($body, $user);
        break;
    case 'GET reservations':
        $user = getAuthUser();
        if (!$id) {
            Response::error('ID da viagem é obrigatório.', 422);
        }
        $reservationController->index($id, $user);
        break;
    case 'GET weather':
        $location = $_GET['location'] ?? null;
        if (!$location) {
            Response::error('Parâmetro location obrigatório.', 422);
        }
        $weather = WeatherService::fetchWeather($location);
        if (!$weather) {
            Response::error('Não foi possível buscar o clima.', 502);
        }
        Response::json(['success' => true, 'weather' => $weather]);
        break;
    case 'GET admin':
        if (($segments[1] ?? '') === 'users') {
            $user = getAuthUser();
            if ($user['type'] !== 'admin') {
                Response::error('Acesso negado.', 403);
            }
            $adminController->users();
        }
        Response::error('Rota admin inválida.', 404);
        break;
    case 'PUT admin':
        if (($segments[1] ?? '') === 'users' && isset($segments[2])) {
            $user = getAuthUser();
            if ($user['type'] !== 'admin') {
                Response::error('Acesso negado.', 403);
            }
            $adminController->updateUser((int)$segments[2], $body);
        }
        Response::error('Rota admin inválida.', 404);
        break;
    case 'DELETE admin':
        if (($segments[1] ?? '') === 'users' && isset($segments[2])) {
            $user = getAuthUser();
            if ($user['type'] !== 'admin') {
                Response::error('Acesso negado.', 403);
            }
            $adminController->deleteUser((int)$segments[2], $user);
        }
        Response::error('Rota admin inválida.', 404);
        break;
    case 'GET exports':
        $user = getAuthUser();
        if (($segments[1] ?? '') === 'trips-pdf') {
            if ($user['type'] !== 'admin') {
                Response::error('Acesso negado.', 403);
            }
            $trips = $tripController->exportAll();
            ExportService::pdf($trips);
        }
        if (($segments[1] ?? '') === 'expenses-csv' && isset($segments[2])) {
            $expenseController->exportCsv((int)$segments[2], $user);
        }
        Response::error('Rota de exportação inválida.', 404);
        break;
    default:
        Response::error('Endpoint não encontrado.', 404);
}
