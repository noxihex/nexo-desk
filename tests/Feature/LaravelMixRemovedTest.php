<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LaravelMixRemovedTest extends TestCase
{
    public function test_mix_configuration_sources_and_generated_assets_are_absent(): void
    {
        $this->assertFileDoesNotExist(base_path('webpack.mix.js'));
        $this->assertFileDoesNotExist(resource_path('js/app.js'));
        $this->assertFileDoesNotExist(resource_path('js/bootstrap.js'));
        $this->assertDirectoryDoesNotExist(resource_path('sass'));
        $this->assertDirectoryDoesNotExist(public_path('js'));
        $this->assertDirectoryDoesNotExist(public_path('css'));
        $this->assertFileDoesNotExist(public_path('mix-manifest.json'));
    }

    public function test_package_manifest_exposes_only_the_vite_pipeline(): void
    {
        $package = json_decode(File::get(base_path('package.json')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame([
            'dev' => 'vite',
            'build' => 'vite build',
        ], $package['scripts']);

        foreach ([
            '@popperjs/core',
            'axios',
            'bootstrap',
            'concurrently',
            'laravel-mix',
            'lodash',
            'postcss',
            'resolve-url-loader',
            'sass',
            'sass-loader',
        ] as $legacyDependency) {
            $this->assertArrayNotHasKey($legacyDependency, $package['devDependencies']);
        }

        foreach (['@tailwindcss/vite', 'laravel-vite-plugin', 'tailwindcss', 'vite'] as $viteDependency) {
            $this->assertArrayHasKey($viteDependency, $package['devDependencies']);
        }
    }

    public function test_lock_file_does_not_contain_direct_mix_pipeline_packages(): void
    {
        $lock = File::get(base_path('package-lock.json'));

        foreach ([
            'node_modules/axios',
            'node_modules/bootstrap',
            'node_modules/concurrently',
            'node_modules/laravel-mix',
            'node_modules/resolve-url-loader',
            'node_modules/sass-loader',
        ] as $legacyPackage) {
            $this->assertStringNotContainsString('"'.$legacyPackage.'"', $lock);
        }
    }
}
