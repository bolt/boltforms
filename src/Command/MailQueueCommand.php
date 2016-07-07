<?php

namespace Bolt\Extension\Bolt\BoltForms\Command;

use Bolt\Filesystem\Exception\IOException;
use Bolt\Filesystem\Handler\DirectoryInterface;
use Bolt\Filesystem\Handler\FileInterface;
use Bolt\Nut\BaseCommand;
use Carbon\Carbon;
use Swift_FileSpool as SwiftFileSpool;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;
use Swift_Transport_SpoolTransport as SwiftTransportSpoolTransport;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Mail queue management command.
 *
 * Copyright (c) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class MailQueueCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('boltforms:mailqueue')
            ->setDescription('Manage the BoltForms mail queue.')
            ->addArgument('clear', InputArgument::OPTIONAL, 'Clear all un-sent message files from the queue. USE WITH CAUTION!')
            ->addArgument('flush', InputArgument::OPTIONAL, 'Flush (send) any queued emails.')
            ->addArgument('recover', InputArgument::OPTIONAL, 'Attempt to restore any incomplete email to a valid state.')
            ->addArgument('show', InputArgument::OPTIONAL, 'Show any queued emails.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var SwiftMailer $mailer */
        $mailer = $this->app['boltforms.mailer'];
        /** @var SwiftTransportSpoolTransport $transport */
        $transport = $mailer->getTransport();
        /** @var SwiftFileSpool $spool */
        $spool = $transport->getSpool();

        if ($input->getArgument('show')) {
            $this->showQueue($output);
        } elseif ($input->getArgument('recover')) {
            $output->write('<info>Attempting recovery of failed email messages to the queue…</info>');
            $spool->recover();
            $output->writeln('<info>  [OK]</info>');
        } elseif ($input->getArgument('flush')) {
            $output->write('<info>Flushing queued emails…</info>');
            $spool->flushQueue($this->app['swiftmailer.transport']);
            $output->writeln('<info>  [OK]</info>');
        } elseif ($input->getArgument('clear')) {
            $output->writeln('<info>Deleting un-sent emails from the queue…</info>');
            $this->clearQueue($output);
        }
    }

    /**
     * Delete any unsent messages from the queue.
     * 
     * @param OutputInterface $output
     */
    protected function clearQueue(OutputInterface $output)
    {
        $failed = 0;
        $messages = 0;
        $spoolCache = $this->app['filesystem']->getFilesystem('cache');
        foreach ($spoolCache->listContents('.spool', false) as $item) {
            if ($item instanceof DirectoryInterface) {
                continue;
            }
            /** @var FileInterface $item */
            if ($item->getExtension() === 'message') {
                try {
                    $item->delete();
                    $messages++;
                } catch (IOException $e) {
                    $failed++;
                }
            }
        }
        $table = new Table($output);
        $table->addRows([
            ['Deleted', $messages],
            ['Failed', $failed],
        ]);
        $table->render();
    }

    /**
     * Show a table of queued messages.
     *
     * @param OutputInterface $output
     */
    protected function showQueue(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('<info>Currently queued emails:</info>');

        $table = new Table($output);
        $table->setHeaders(['', 'Date', 'Address', 'Subject']);

        $i = 1;
        $spoolCache = $this->app['filesystem']->getFilesystem('cache');
        foreach ($spoolCache->listContents('.spool', false) as $item) {
            if ($item instanceof DirectoryInterface) {
                continue;
            }
            /** @var FileInterface $item */
            if ($item->getExtension() !== 'message') {
                continue;
            }

            /** @var SwiftMessage $message */
            $message = unserialize($item->readStream());
            if ($message) {
                $to = $message->getTo();
                $subject = $message->getSubject();
                $date = Carbon::createFromTimestamp($message->getDate())->format('c');

                $table->addRow([$i++, $date, sprintf('%s <%s>', current($to), key($to)), $subject]);
            }
        }
        $table->render();
    }
}
