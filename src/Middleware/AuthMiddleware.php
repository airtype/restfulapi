<?php

namespace RestfulApi\Middleware;

use RestfulApi\Http\Request;
use RestfulApi\Http\Response;
use RestfulApi\Exceptions\RestfulApiException;
use Craft\UserModel;

class AuthMiddleware
{
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $user             = \Craft\craft()->userSession->getUser();
        $is_authenticated = $this->isAuthenticated($request, $user);

        $this->validateRoute($request, $user, $is_authenticated);

        return $next($request, $response);;
    }

    /**
     * Validate Route
     *
     * @param Request   $request          Request
     * @param UserModel $user             User
     * @param bool      $is_authenticated Is Authenticated
     *
     * @return void
     */
    private function validateRoute(Request $request, UserModel $user = null, $is_authenticated = false)
    {
        $this->validateElementType($request->getAttribute('elementType'), array_keys(\Craft\craft()->elements->getAllElementTypes()));

        // if (!empty($request->getAttribute('elementId'))) {
        //     $this->validateElement($request->getAttribute('elementId'), $request->getAttribute('elementType'));
        // }

        $this->validateElementPermission($request, $user, $is_authenticated);
    }

    /**
     * Validate Element Type
     *
     * @param string $element_type  Element Type
     * @param array  $element_types Element Types
     *
     * @return null|RestfulApiException
     */
    private function validateElementType($element_type, $element_types)
    {
        if ($element_type && !in_array($element_type, $element_types)) {
            throw new RestfulApiException(sprintf('`%s` is not a valid element type.', $element_type));
        }
    }

    /**
     * Validate Element
     *
     * @param string $element_id   Element Id
     * @param string $element_type Element Type
     *
     * @return null|RestfulApiException
     */
    private function validateElement($element_id, $element_type)
    {
        $element = \Craft\craft()->elements->getElementById($element_id);

        if (!$element) {
            $exception = new RestfulApiException();
            $exception
                ->setStatus(404)
                ->setMessage(sprintf('An element with an id of `%s` was not found.', $element_id));

            throw $exception;
        }

        if ($element->getElementType() !== $element_type) {
            $exception = new RestfulApiException();
            $exception
                ->setStatus(404)
                ->setMessage(sprintf('An element with an id of `%s` was found, but the element type is `%s`, not `%s`.', $element_id, $element->getElementType(), $element_type));

            throw $exception;
        }
    }

    /**
     * Validate Element Permission
     *
     * @param Request   $request          Request
     * @param UserModel $user             User
     * @param bool      $is_authenticated Is Authenticated
     *
     * @return null|RestfulApiException
     */
    private function validateElementPermission(Request $request, UserModel $user = null, $is_authenticated = false)
    {
        $element_permissions = \Craft\craft()->restfulApi_config->getElementPermissions($request->getAttribute('elementType'));

        if ($is_authenticated && in_array($request->getMethod(), $element_permissions['authenticated'])) {
            return;
        }

        if (in_array($request->getMethod(), $element_permissions['public'])) {
            return;
        }

        $exception = new RestfulApiException();
        $exception
            ->setStatus(401)
            ->setMessage(sprintf('User is not authorized to perform method `%s` on `%s` element type.', $request->getMethod(), $request->getAttribute('elementType')));

        throw $exception;
    }

    /**
     * Is Authenticated
     *
     * @return boolean
     */
    private function isAuthenticated(Request $request, UserModel $user = null)
    {
        $auth = \Craft\craft()->config->get('defaultAuth', 'restfulApi');

        if (!$auth) {
            return false;
        }

        if ($auth === 'basicAuth') {
            return $this->validateBasicAuth($request);
        }

        if ($auth === 'craft' && $user) {
            return $this->validateCraftAuth($request, $user);
        }

        return false;
    }

    /**
     * Validate Basic Auth
     *
     * @param Request $request Request
     *
     * @return boolean
     */
    private function validateBasicAuth(Request $request)
    {
        $basic_auth = \Craft\craft()->config->get('auth', 'restfulApi')['basicAuth'];

        $username = $request->getServerParam('PHP_AUTH_USER');
        $password = $request->getServerParam('PHP_AUTH_PW');

        if ($basic_auth['username'] === $username && $basic_auth['password'] === $password) {
            return true;
        }

        return false;
    }

    /**
     * Validate Craft Auth
     *
     * @param Request   $request Request
     * @param UserModel $user    User
     *
     * @return boolean
     */
    private function validateCraftAuth(Request $request, UserModel $user)
    {
        $craft_auth = \Craft\craft()->config->get('auth', 'restfulApi')['craft'];

        if (in_array($user->username, $craft_auth['users'])) {
            return true;
        }

        foreach ($craft_auth['permissions'] as $permission) {
            if (\Craft\craft()->userPermissions->doesUserHavePermission($user->id, $permission)) {
                return true;
            }
        }

        foreach ($craft_auth['groups'] as $group) {
            if ($user->isInGroup($group)) {
                return true;
            }
        }

        return false;
    }
}
