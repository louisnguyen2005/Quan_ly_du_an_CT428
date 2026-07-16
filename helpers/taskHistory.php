<?php

if (!function_exists('log_task_history')) {
    /**
     * Records one row in task_history. Used by both the Staff workflow
     * actions (start/pause/resume/submit) and the Manager review actions
     * (approve/reject) so every task keeps a single, consistent timeline.
     */
    function log_task_history(
        PDO $pdo,
        int $taskId,
        ?int $userId,
        string $actionType,
        ?string $fromValue = null,
        ?string $toValue = null,
        ?string $note = null
    ): void {
        $stmt = $pdo->prepare("
            INSERT INTO task_history (task_id, user_id, action_type, from_value, to_value, note)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$taskId, $userId, $actionType, $fromValue, $toValue, $note]);
    }
}
