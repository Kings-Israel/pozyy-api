<?php
use Symfony\Component\HttpFoundation\Response;

function pozzy_httpOk($message) {
    return response()->json(
        [
            'code' => Response::HTTP_OK,
            'message' => $message,
        ], Response::HTTP_OK
    );
}

function pozzy_httpCreated($message) {
    return response()->json(
        [
            'code' => Response::HTTP_CREATED,
            'message' => $message
        ], Response::HTTP_CREATED
    );
}

function pozzy_httpNotFound($message) {
    return response()->json(
        [
            'code' => Response::HTTP_NOT_FOUND,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND
    );
}

function pozzy_httpBadRequest($message) {
    return response()->json(
        [
            'code' => Response::HTTP_BAD_REQUEST,
            'message' => $message,
        ], Response::HTTP_BAD_REQUEST
    );
}

function pozzy_httpForbidden($message) {
    return response()->json(
        [
            'code' => Response::HTTP_FORBIDDEN,
            'message' => $message,
        ], Response::HTTP_FORBIDDEN
    );
}