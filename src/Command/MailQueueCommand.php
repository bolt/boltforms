<?php

namespace Bolt\Extension\Bolt\BoltForms\Command;

use Bolt\Nut\BaseCommand;
use Carbon\Carbon;
use DirectoryIterator;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;
use Swift_FileSpool as SwiftFileSpool;
use Swift_Transport_SpoolTransport as SwiftTransportSpoolTransport;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('flush', null, InputOption::VALUE_NONE,'Flush (send) any queued emails.')
            ->addOption('recover', null, InputOption::VALUE_NONE,'Attempt to restore any incomplete email to a valid state.')
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

        if ($input->getOption('flush') === false && $input->getOption('recover') === false) {
            $output->writeln('');
            $output->writeln('<info>Currently queued emails:</info>');

            $table = new Table($output);
            $table->setHeaders(['', 'Date', 'Address', 'Subject']);

            $i = 1;
            $spoolPath = $this->app['resources']->getPath('cache/.spool');
            $directoryIterator = new DirectoryIterator($spoolPath);
            foreach ($directoryIterator as $file) {
                $fileName = $file->getRealPath();
                if (substr($fileName, -8) != '.message') {
                    continue;
                }
                /** @var SwiftMessage $message */
                $message = unserialize(file_get_contents($fileName));
                $to = $message->getTo();
                $subject = $message->getSubject();
                $date = Carbon::createFromTimestamp($message->getDate())->format('c');

                $table->addRow([$i++, $date, sprintf('%s <%s>', current($to), key($to)), $subject]);
            }

            $table->render();

            return;
        }

        if ($input->getOption('recover')) {
            $output->write('<info>Attempting recovery of failed email messages to the queue…</info>');
            $spool->recover();
        }

        if ($input->getOption('flush')) {
            $output->write('<info>Flushing queued emails…</info>');
            $spool->flushQueue($this->app['swiftmailer.transport']);
        }

        $output->writeln('<info>  [OK]</info>');
    }
}
