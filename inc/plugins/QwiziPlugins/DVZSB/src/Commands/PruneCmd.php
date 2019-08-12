<?php

declare(strict_types=1);

namespace Qwizi\DVZSB\Commands;

use Qwizi\DVZSB\Interfaces\ModRequiredInterface;

class PruneCmd extends AbstractCommandBase implements ModRequiredInterface
{
    private $pattern = "/^({command}|{command}[\s](--all|(.*)))$/";

    public function doAction(array $data): void
    {
        if (preg_match($this->createPattern($data['command'], $this->pattern), $data['text'], $matches)) {

            $this->lang->load('dvz_shoutbox_bot_prune');

            if ($matches[2] == "--all") {

                $this->setSendMessage(false);
                $this->deleteShout();
                $this->run_hook('dvz_shoutbox_bot_commands_prune_all_commit');
            } else {
                $user = get_user((int) $data['uid']);
                $target = get_user_by_username($matches[2], ['fields' => 'uid, username']);

                if (!$this->isValidUser($user) || !$this->isValidUser($target)) {
                    $this->setError($this->lang->error_empty_user);
                }

                if (!$this->getError()) {
                    $this->deleteShout("uid={$target['uid']}");
                    $this->setSendMessage(true);
                    $this->lang->message_success = $this->lang->sprintf($this->lang->message_success, "@\"{$user['username']}\"", "@\"{$target['username']}\"");

                    $this->setMessage($this->lang->message_success);
                }


                $this->send()->setReturnedValue([
                    'uid' => $user['uid'],
                    'tuid' => $target['uid'],
                    'message' => $this->getMessage(),
                    'error' => $this->getError(),
                ])->run_hook('dvz_shoutbox_bot_commands_prune_commit');
            }
        }
    }
}
