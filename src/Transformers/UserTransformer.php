<?php

namespace RestfulApi\Transformers;

use Craft\UserModel;

class UserTransformer extends BaseTransformer
{
    /**
     * Transform
     *
     * @param UserModel $element User
     *
     * @return array User
     */
    public function transform(UserModel $element)
    {
        return [
            'id'                         => (int) $element->id,
            'enabled'                    => (int) $element->enabled,
            'archived'                   => (int) $element->archived,
            'locale'                     => $element->locale,
            'localeEnabled'              => (int) $element->localeEnabled,
            'slug'                       => $element->slug,
            'uri'                        => $element->uri,
            'dateCreated'                => $element->dateCreated,
            'dateUpdated'                => $element->dateUpdated,
            'root'                       => ($element->root) ? (int) $element->root : null,
            'lft'                        => ($element->lft) ? (int) $element->lft : null,
            'rgt'                        => ($element->rgt) ? (int) $element->rgt : null,
            'level'                      => ($element->level) ? (int) $element->level : null,
            'username'                   => $element->username,
            'firstName'                  => $element->firstName,
            'lastName'                   => $element->lastName,
            'email'                      => $element->email,
            // 'password'                   => $element->password,
            'preferredLocale'            => $element->preferredLocale,
            'weekStartDay'               => $element->weekStartDay,
            'admin'                      => (int) $element->admin,
            'client'                     => (int) $element->client,
            'locked'                     => (int) $element->locked,
            'suspended'                  => (int) $element->suspended,
            'pending'                    => (int) $element->pending,
            'lastLoginDate'              => $element->lastLoginDate,
            'invalidLoginCount'          => $element->invalidLoginCount ? (int) $element->invalidLoginCount : $element->invalidLoginCount,
            'lastInvalidLoginDate'       => $element->lastInvalidLoginDate,
            'lockoutDate'                => $element->lockoutDate,
            'passwordResetRequired'      => $element->passwordResetRequired,
            'lastPasswordChangeDate'     => $element->lastPasswordChangeDate,
            'unverifiedEmail'            => $element->unverifiedEmail,
            'newPassword'                => $element->newPassword,
            'currentPassword'            => $element->currentPassword,
            'verificationCodeIssuedDate' => $element->verificationCodeIssuedDate,
        ];
    }
}
