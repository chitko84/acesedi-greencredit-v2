<?php
function gc_relative_web_path($fromDir, $toFile)
{
    $from = realpath($fromDir);
    $to = realpath($toFile);

    if ($from === false || $to === false) {
        return str_replace('\\', '/', $toFile);
    }

    $fromParts = explode(DIRECTORY_SEPARATOR, rtrim($from, DIRECTORY_SEPARATOR));
    $toParts = explode(DIRECTORY_SEPARATOR, rtrim($to, DIRECTORY_SEPARATOR));

    while ($fromParts && $toParts && $fromParts[0] === $toParts[0]) {
        array_shift($fromParts);
        array_shift($toParts);
    }

    return str_replace('\\', '/', str_repeat('../', count($fromParts)) . implode('/', $toParts));
}

function gc_profile_image_src($profilePic)
{
    $projectRoot = dirname(__DIR__);
    $currentDir = dirname($_SERVER['SCRIPT_FILENAME'] ?? __FILE__);
    $profilePic = trim(str_replace('\\', '/', (string) $profilePic));
    $profilePic = !empty($profilePic) ? $profilePic : 'default-profile.jpg';
    $profilePic = preg_replace('#^(\.\./)+#', '', ltrim($profilePic, '/'));

    $candidates = [];

    if ($profilePic === 'default-profile.jpg') {
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'default-profile.jpg';
    } elseif (strpos($profilePic, 'user/uploads/') === 0 || strpos($profilePic, 'uploads/') === 0) {
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $profilePic);
    } else {
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($profilePic);
        $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($profilePic);
    }

    $default = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'default-profile.jpg';
    foreach ($candidates as $candidate) {
        if (file_exists($candidate)) {
            return gc_relative_web_path($currentDir, $candidate);
        }
    }

    return gc_relative_web_path($currentDir, $default);
}
