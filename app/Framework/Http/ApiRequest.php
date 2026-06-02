<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Framework\Http;

/**************************************************************************************
 * API Request Handler
 *
 * This class provides utilities for working with API requests in the Catalyst framework.
 * It helps identify API requests and extract data from different request formats,
 * supporting JSON, form-urlencoded and multipart form data content types.
 *
 * The class provides a standardized way to handle different types of API requests
 * regardless of their source or format, simplifying API development within the framework.
 *
 * @package Catalyst\Framework\Http
 */
/**
 * Defines the Api Request class contract.
 *
 * @package Catalyst\Framework\Http
 * Responsibility: Coordinates the api request behavior within its module boundary.
 */
class ApiRequest
{
    /**
     * Procesa una petición API, extrayendo datos según el formato
     *
     * @param Request $request La petición original
     * @return array Los datos de la petición
     */
    public static function getData(Request $request): array
    {
        $contentType = $request->getHeaders('Content-Type');

        if ($contentType && str_contains($contentType, 'application/json')) {
            // JSON request body
            return json_decode($request->getContent(), true) ?? [];
        } elseif ($contentType && str_contains($contentType, 'application/x-www-form-urlencoded')) {
            // Form URL-encoded
            return $request->getAllPost();
        } elseif ($contentType && str_contains($contentType, 'multipart/form-data')) {
            // Multipart form data
            return $request->getAllPost();
        }

        // Default fallback
        return $request->getAllPost();
    }

    /**
     * Determina si la petición es una API request
     *
     * @param Request $request La petición
     * @return bool True si es API request
     */
    public static function isApiRequest(Request $request): bool
    {
        // Check X-Requested-With header (standard AJAX indicator)
        $requestedWith = $request->getHeaders('X-Requested-With');
        if ($requestedWith && strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }

        // Check Accept header for application/json
        $accept = $request->getHeaders('Accept');
        if ($accept && str_contains($accept, 'application/json')) {
            return true;
        }

        // Check Content-Type for application/json
        $contentType = $request->getHeaders('Content-Type');
        if ($contentType && str_contains($contentType, 'application/json')) {
            return true;
        }

        return false;
    }
}
