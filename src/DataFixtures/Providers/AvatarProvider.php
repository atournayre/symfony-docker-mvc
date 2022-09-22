<?php

namespace App\DataFixtures\Providers;

use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[When(env: 'dev')]
class AvatarProvider
{
    public const FILENAME_PREFIX = 'fixture_';

    public function avatar(string $identifiant): ?string
    {
        $fixturesAvatarDir = realpath(__DIR__.'/../../../fixtures/avatars');
        $finder = new Finder();
        $finder
            ->files()
            ->in($fixturesAvatarDir);

        if(!$finder->hasResults()) return null;

        /** @var SplFileInfo[] $avatars */
        $avatars = iterator_to_array($finder->getIterator());
        $avatar = $avatars[array_rand($avatars)];

        $avatarPath = realpath($fixturesAvatarDir.'/'.$avatar->getFilename());

        $publicAvatarDir = realpath(__DIR__.'/../../../public/avatars/uploads');

        $finalFilename = sprintf(
            '%s.%s',
            md5($identifiant),
            $avatar->getExtension()
        );
        $avatarPathFixtures = $publicAvatarDir.'/'.$finalFilename;

        $fs = new Filesystem();

        if(!$fs->exists($avatarPath)) return null;

        $fs->copy($avatarPath, $avatarPathFixtures, true);

        return $finalFilename;
    }
}
