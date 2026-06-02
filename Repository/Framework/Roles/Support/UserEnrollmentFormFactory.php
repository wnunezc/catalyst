<?php

declare(strict_types=1);

namespace Catalyst\Repository\Roles\Support;

use Catalyst\Framework\Admin\Form\FormBuilder;
use Catalyst\Framework\Authorization\RoleRepository;

final class UserEnrollmentFormFactory
{
    /**
     * @return array<string, mixed>
     */
    public function build(RoleRepository $roles): array
    {
        $roleOptions = [];
        foreach ($roles->allRoles() as $role) {
            $slug = (string) ($role['slug'] ?? '');
            if ($slug !== '') {
                $roleOptions[$slug] = RbacLabelPresenter::roleName((string) ($role['name'] ?? ''), $slug) . ' — ' . $slug;
            }
        }

        if ($roleOptions === []) {
            $roleOptions['user'] = (string) __('roles.users.form.default_role_label');
        }

        return FormBuilder::make()
            ->action('/users/enroll')
            ->method('POST')
            ->wrapperClass('row g-3 admin-enrollment-form')
            ->sections([
                'identity' => ['title' => __('roles.users.enroll.sections.identity_title'), 'description' => __('roles.users.enroll.sections.identity_description')],
                'security' => ['title' => __('roles.users.enroll.sections.security_title'), 'description' => __('roles.users.enroll.sections.security_description')],
                'access' => ['title' => __('roles.users.enroll.sections.access_title'), 'description' => __('roles.users.enroll.sections.access_description')],
            ])
            ->fields([
                'name' => ['label' => __('roles.users.form.labels.name'), 'required' => true, 'section' => 'identity', 'col_class' => 'col-12 col-xl-6', 'placeholder' => __('roles.users.form.placeholders.name'), 'attributes' => ['maxlength' => 255, 'autocomplete' => 'name']],
                'email' => ['label' => __('roles.users.form.labels.email'), 'required' => true, 'section' => 'identity', 'col_class' => 'col-12 col-xl-6', 'type' => 'email', 'placeholder' => __('roles.users.form.placeholders.email'), 'attributes' => ['maxlength' => 255, 'autocomplete' => 'email']],
                'password' => ['label' => __('roles.users.form.labels.password'), 'required' => true, 'section' => 'security', 'col_class' => 'col-12 col-xl-6', 'type' => 'password', 'placeholder' => __('roles.users.form.placeholders.password'), 'help' => __('roles.users.form.help.password'), 'attributes' => ['autocomplete' => 'new-password', 'minlength' => 8], 'value' => ''],
                'password_confirm' => ['label' => __('roles.users.form.labels.password_confirm'), 'required' => true, 'section' => 'security', 'col_class' => 'col-12 col-xl-6', 'type' => 'password', 'placeholder' => __('roles.users.form.placeholders.password_confirm'), 'attributes' => ['autocomplete' => 'new-password', 'minlength' => 8], 'value' => ''],
                'role' => ['label' => __('roles.users.form.labels.role'), 'required' => true, 'section' => 'access', 'col_class' => 'col-12 col-xl-6', 'type' => 'select', 'options' => $roleOptions, 'help' => __('roles.users.form.help.role')],
                'email_verified' => ['label' => __('roles.users.form.labels.email_verified'), 'section' => 'access', 'col_class' => 'col-12 col-xl-6', 'type' => 'select', 'options' => ['1' => __('roles.users.form.options.email_verified_yes'), '0' => __('roles.users.form.options.email_verified_no')], 'help' => __('roles.users.form.help.email_verified')],
            ])
            ->actions([
                ['type' => 'submit', 'label' => __('roles.users.form.actions.submit'), 'class' => 'btn btn-primary btn-sm', 'icon' => 'fa-solid fa-user-plus'],
                ['type' => 'link', 'label' => __('roles.common.cancel'), 'href' => '/users', 'class' => 'btn btn-outline-secondary btn-sm'],
            ])
            ->toArray();
    }
}
