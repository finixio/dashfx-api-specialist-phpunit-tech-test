<?php

/** @noinspection JsonEncodingApiUsageInspection */
/*
 * This is meant to be representative of a basic broker api, the code is basic
 * it's just here to serve as a fake broker api for the DashFx integration.
 *
 * Import the following into postman if you want to test. make sure to run `make broker-up` first

curl --location --request POST 'http://localhost:10101' \
    --header 'Authorization: s3cr3tk3y' \
    --header 'Content-Type: application/json' \
    --data-raw '{
        "firstName" : "test",
        "lastName" : "test",
        "email" : "test@test.com",
        "phone": "+493022610",
        "country": "GB",
        "ip": "93.241.38.109"}'

 */

declare(strict_types=1);

// if this is true, then the api will pretend the user already exists and throw an error
$userAlreadyExists = (bool) random_int(0, 1);

// super secret key that must be sent over headers
$apiKey = 's3cr3tk3y';

/**
 * Get JSON post as array, throws exception if unable to parse.
 *
 * @return array
 *
 * @throws RuntimeException
 */
function getJsonPost(): array
{
    $jsonDataStr = file_get_contents('php://input');
    $jsonData    = json_decode($jsonDataStr, true);

    return JSON_ERROR_NONE === json_last_error()
        ? $jsonData
        : throw new RuntimeException('Unable to parse JSON data. Please send leads as a JSON POST');
}

/**
 * Returns a response in JSON along with appropriate headers.
 *
 * @param mixed $responseData
 * @param int   $httpStatus
 *
 * @return mixed
 */
function response(mixed $responseData, int $httpStatus): mixed
{
    // clear the old headers
    header_remove();
    // set the actual code
    http_response_code($httpStatus);
    // treat this as json
    header('Content-Type: application/json');

    $response = match (true) {
        is_string($responseData) => json_encode(['error' => $responseData], JSON_UNESCAPED_SLASHES),
        is_object($responseData) => json_encode((array) $responseData, JSON_UNESCAPED_SLASHES),
        default                  => json_encode($responseData, JSON_UNESCAPED_SLASHES),
    };

    error_log(sprintf('[HTTP %d] response: %s', $httpStatus, $response));

    echo $response;

    exit;
}

// the fields we accept
$inputValidation = [
    'firstName' => [
        'filter'  => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => '/^[a-z0-9 ]+$/i'],
    ],
    'lastName' => [
        'filter'  => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => '/^[a-z0-9 ]+$/i'],
    ],
    'email' => FILTER_VALIDATE_EMAIL,
    'phone' => [
        'filter'  => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => '/^[+0-9() ]+$/i'],
    ],
    'country' => [
        'filter'  => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => '/^[A-Z]{2}$/i'],
    ],
    'ip' => FILTER_VALIDATE_IP,
];

// main program
try {
    // first check for valid header
    if ($apiKey !== $_SERVER['HTTP_AUTHORIZATION']) {
        return response('Missing key. Please send key in `Authorization` header.', 400);
    }
    // Get the users JSON input and perform initial validation
    $requiredFields = array_keys($inputValidation);
    $jsonFields     = filter_var_array(getJsonPost(), $inputValidation, false);

    // validate that we have all fields
    $jsonValidation = array_filter($requiredFields, static fn (string $f) => !empty($jsonFields[$f]));

    // if we don't display an error
    if (count($jsonValidation) < count($requiredFields)) {
        return response([
            'error'  => 'Some fields are missing or invalid, please see the fields object.',
            'fields' => [
                array_values(array_diff($requiredFields, $jsonValidation)),
            ],
        ], 400);
    }

    // we have a valid post
    return true === $userAlreadyExists
        ? response([
            'error'  => 'Duplicate User! This user already exists in our system',
            'fields' => ['email'],
        ], 400)
        : response([
        'success' => sprintf('The user %s %s using email %s has been signed up to the broker.',
            $jsonFields['firstName'],
            $jsonFields['lastName'],
            $jsonFields['email'],
        ),
        'depositUrl' => 'https://google.com/foo?userId='.random_int(100, 100000),
    ], 200);
} catch (Exception|Error $e) {
    return response($e->getMessage(), 400);
}
