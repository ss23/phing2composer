#!/usr/bin/env php
<?php

require(dirname(__FILE__) . '/JsonFile.php');

if ($argc > 3) {
        usage();
}

$phingFile = (empty($argv[1]) ? 'dependent-modules' : $argv[1]);
$composerFile = (empty($argv[2]) ? 'composer.json' : $argv[2]);

if (!file_exists($phingFile)) {
        echo $phingFile . " does not exist\r\n";
        usage();
}

$jasons = array();

// Each line of dependent files is 'independant', so we can process it one by one and build up an array of required modules etc
foreach (file($phingFile, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES) as $line) {
        if ($line[0] == '#')
                continue; // Don't bother parsing coments

        try {
                $data = explode(' ', $line);
                // If the module is on github, we can presume (probably incorrectly) that it exists in the repos for composer
                // If gitorious, we can assume it's a fork (and hopefully get it back onto a non forked version)
                if (strpos($data[1], 'github') !== FALSE) {
                        $jasons[] = parse_as_official($data);
                } else if (strpos($data[1], 'gitorious') !== FALSE) {
                        $jasons[] = parse_as_fork($data);
                } else {
                        throw new Exception('Count not parse line from phing: ' . $data[0] . ' - ' . $data[1]);
                }
        } catch (Exception $e) {
                echo "Oh no! An exception. Hmph. Guess you'll have to add that one yourself: " . $line . "\r\n";
                echo $e . "\r\n";
        }
}

// Output the jasons
$output_me_harder = array(
        'name' => 'name-me',
        'description' => 'Converted from phing to composer',
        'require' => array(
                // Probably should find a way to include a PHP version too, but Silverstripe should do that itself as required
        ),
        'repositories' => array(),
        'minimum-stability' => 'dev',
);

foreach ($jasons as $repo) {
        $output_me_harder['require'][$repo['module']] = $repo['version'];
        if ($repo['type'] == 'fork') {
                // Add a repository selector thingy
                $output_me_harder['repositories'][] = array(
                        'type' => 'package',
                        'package' => array(
                                'name' => $repo['module'],
                                'version' => $repo['version'],
                                'source' => array(
                                        'url' => $repo['url'],
                                        'type' => 'git',
                                        'reference' => 'master', // Going to have to fix this, will take patches
                                ),
                        ),
                );
        }
}

echo JsonFile::encode($output_me_harder, JsonFile::JSON_PRETTY_PRINT | JsonFile::JSON_UNESCAPED_SLASHES);

echo "\r\n";

function parse_as_official($data) {
        $data[0] = explode(':', $data[0]);
        $module = 'silverstripe/' . $data[0][0]; // e.g. silverstripe/framework
        $version = phingversion2composer($data[0][1]); // e.g. 3.0
        return array('module' => $module, 'version' => $version, 'type' => 'official');
}

function parse_as_fork($data) {
        $data[0] = explode(':', $data[0]);
        $version = phingversion2composer($data[0][1]); // e.g. 3.0
        $module = 'custom-silvestripe-fork/' . $data[0][0];
        $url = $data[1];
        return array('module' => $module, 'version' => $version, 'url' => $url, 'type' => 'fork');
}

function phingversion2composer($version) {
        if ($version == 'master') {
                return 'dev-master';
        }
        $version = explode('.', $version);

        if ((count($version) < 2) or (count($version) > 3)) {
                throw new Exception('Got a version we could not parse: ' . var_export($version, true));
        }

        if (count($version) == 2 || ($version[2] == '0')) {
                // Composer seems to *love* being x.x.x
                return $version[0] . $version[1] . '.x-dev';
        }

        return implode('.', $version);
}


function usage() {
        die("usage: ./phing2composer [dependent-modules] [composer.json]\r\n");
}
