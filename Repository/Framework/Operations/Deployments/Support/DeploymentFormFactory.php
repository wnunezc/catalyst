<?php

declare(strict_types=1);

namespace Catalyst\Repository\Operations\Deployments\Support;

use Catalyst\Framework\Form\FormBuilder;

/**
 * Builds the deployment execution form from the configured profile allowlist.
 */
final class DeploymentFormFactory
{
    /**
     * @param array<string, array<string, mixed>> $profiles
     * @return array<string, mixed>
     */
    public function build(array $profiles): array
    {
        $options = [];
        foreach ($profiles as $key => $profile) {
            $options[] = [
                'value' => $key,
                'label' => $key . ' - ' . (string) ($profile['description'] ?? ''),
            ];
        }

        return FormBuilder::make()
            ->action('/operations/deployments/runs')
            ->method('POST')
            ->sections([
                'release' => [
                    'title' => __('operations.deployments.form.title'),
                    'description' => __('operations.deployments.form.description'),
                ],
            ])
            ->fields([
                'profile_key' => [
                    'label' => __('operations.deployments.form.fields.profile'),
                    'type' => 'select',
                    'required' => true,
                    'section' => 'release',
                    'empty_option_label' => __('operations.deployments.form.fields.select_profile'),
                    'options' => $options,
                ],
                'dry_run' => [
                    'label' => __('operations.deployments.form.fields.dry_run'),
                    'type' => 'checkbox',
                    'section' => 'release',
                    'help' => __('operations.deployments.form.fields.dry_run_help'),
                ],
            ])
            ->actions([[
                'type' => 'submit',
                'label' => __('operations.deployments.form.actions.run'),
                'class' => 'btn btn-primary',
            ]])
            ->toArray();
    }
}
