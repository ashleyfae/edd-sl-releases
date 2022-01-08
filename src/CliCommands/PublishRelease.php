<?php
/**
 * PublishRelease.php
 *
 * @package   edd-sl-releases
 * @copyright Copyright (c) 2021, Ashley Gibson
 * @license   GPL2+
 */

namespace EddSlReleases\CliCommands;

use EddSlReleases\Actions\CreateAndPublishRelease;
use EddSlReleases\Services\GitHubApi;

class PublishRelease implements CliCommand
{

    public function __construct(
        protected GitHubApi $gitHubApi,
        protected CreateAndPublishRelease $releasePublisher
    ) {
    }

    public static function commandName(): string
    {
        return 'release';
    }

    /**
     * Publishes a new release.
     *
     * ##  OPTIONS
     *
     * <product_id>
     * : ID of the EDD product to create the release for.
     *
     * <repo>
     * : GitHub repo. Format: {org}/{repo_name}
     *
     * [--tag=<tag>]
     * : Name of the tag. If omitted, most recent release is used.
     *
     * @param  array  $assocArgs
     * @param  array  $args
     */
    public function __invoke(array $assocArgs, array $args): void
    {
        $product = new \EDD_SL_Download($assocArgs[0]);

        if (! $product->ID) {
            \WP_CLI::error(__('Invalid product.', 'edd-sl-releases'));
        }

        /* Translators: %s name of the product */
        \WP_CLI::confirm(sprintf(__('Is this the correct product? %s', 'edd-sl-releases'), $product->post_title));

        try {
            if (! empty($args['tag'])) {
                $release = $this->gitHubApi->getReleaseByTag($assocArgs[1], $args['tag']);
            } else {
                $release = $this->gitHubApi->getLatestRelease($assocArgs[1]);
            }

            if (empty($release['assets'][0])) {
                throw new \Exception('Release has no assets.');
            }

            $asset = $release['assets'][0];

            \WP_CLI::line(json_encode($asset, JSON_PRETTY_PRINT));

            \WP_CLI::confirm(__('Is this asset correct?', 'edd-sl-releases'));

            $releaseArgs = [
                'product_id'   => $product->ID,
                'version'      => sanitize_text_field($release['tag_name']), // could also use 'name'
                'file_url'     => $asset['url'],
                'file_name'    => $asset['name'],
                'changelog'    => wp_kses_post($release['body']),
                'requirements' => null, // @todo
                'pre_release'  => ! empty($asset['prerelease']) ? 1 : 0,
            ];

            \WP_CLI::line(json_encode($releaseArgs, JSON_PRETTY_PRINT));

            \WP_CLI::line(__('Do these arguments look correct?', 'edd-sl-releases'));

            $release = $this->releasePublisher->execute($releaseArgs);

            /* Translators: %d ID of the release */
            \WP_CLI::success(sprintf(__('Successfully created release #%d.', 'edd-sl-releases'), $release->id));
        } catch (\Exception $e) {
            \WP_CLI::error($e->getMessage());
        }
    }
}
