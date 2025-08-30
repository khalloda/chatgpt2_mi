<?php declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Note;
use function App\Core\require_auth;
use function App\Core\verify_csrf_post;
use function App\Core\flash_set;
use function App\Core\redirect;

final class NotesController extends Controller
{
    public function store(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/'); }

        $entityType = (string)($_POST['entity_type'] ?? '');
        $entityId   = (int)($_POST['entity_id'] ?? 0);
        $isPublic   = isset($_POST['is_public']) && $_POST['is_public'] ? 1 : 0;
        $body       = trim((string)($_POST['body'] ?? ''));
        $returnTo   = (string)($_POST['_return'] ?? '/');

        if ($entityType === '' || $entityId <= 0 || $body === '') {
            flash_set('error','Note text is required.');
            redirect($returnTo ?: '/');
        }

        // Try a few common session keys to get the email
        $email = (string)(
            $_SESSION['user_email']
            ?? ($_SESSION['auth_email'] ?? '')
            ?? ($_SESSION['user']['email'] ?? '')
            ?? 'system'
        );
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        Note::create([
            'entity_type'   => $entityType,
            'entity_id'     => $entityId,
            'is_public'     => $isPublic,
            'body'          => $body,
            'created_by'    => $email,
            'created_by_id' => $userId,
        ]);

        flash_set('success','Note added.');
        redirect($returnTo ?: '/');
    }

    public function destroy(): void
    {
        require_auth();
        if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/'); }

        $id = (int)($_POST['id'] ?? 0);
        $returnTo = (string)($_POST['_return'] ?? '/');
        if ($id > 0) {
            Note::delete($id);
            flash_set('success','Note deleted.');
        }
        redirect($returnTo ?: '/');
    }
	
	public function update(): void
{
    require_auth();
    if (!verify_csrf_post()) { flash_set('error','Invalid session.'); redirect('/'); }

    $id       = (int)($_POST['id'] ?? 0);
    $body     = trim((string)($_POST['body'] ?? ''));
    $isPublic = isset($_POST['is_public']) && $_POST['is_public'] ? 1 : 0;
    $returnTo = (string)($_POST['_return'] ?? '/');

    if ($id <= 0 || $body === '') {
        flash_set('error','Note text is required.');
        redirect($returnTo ?: '/');
    }

    Note::update($id, $body, (bool)$isPublic);
    flash_set('success','Note updated.');
    redirect($returnTo ?: '/');
}
	
}
