<?php

/**
 * Secure GitHub Auto-Deploy Webhook Script for cPanel
 */
$secret = 'visiontech-deploy-secret-2026';

if (! isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden: Invalid deploy secret']);
    exit;
}

$repoPath = __DIR__;
if (! file_exists($repoPath.'/.git')) {
    $repoPath = dirname(__DIR__);
}

$sshKey = '/home/visiontech/.ssh/github_deploy_key';
$sshCmd = file_exists($sshKey) ? "git config core.sshCommand \"ssh -i {$sshKey} -o StrictHostKeyChecking=no\" && " : '';

$cmd = "cd {$repoPath} && {$sshCmd}git fetch origin main 2>&1 && git reset --hard origin/main 2>&1";

exec($cmd, $output, $returnCode);

header('Content-Type: application/json');
if ($returnCode === 0) {
    echo json_encode(['success' => true, 'output' => $output]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'output' => $output, 'code' => $returnCode]);
}
