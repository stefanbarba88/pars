<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\SignEmail;
use App\Repository\TaskRepository;
use App\Repository\SignEmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use setasign\Fpdi\Tcpdf\Fpdi as TcpdfFpdi;

class DailyReportService {
    public function __construct(
        private EntityManagerInterface $em,
        private ParameterBagInterface  $parameterBag,
        private MailService            $mail,
        private TaskRepository         $taskRepository,
        private SignEmailRepository    $signEmailRepository,
    ) {
    }

    public function generateAndSendExingReports(): void {
        $client = $this->em->getRepository(Client::class)->find(5);


        $exingPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/exing';
        $attachments = [];

        $projects = $this->em->getRepository(Project::class)->findBy(['isSuspended' => false]);
        $danas = new \DateTimeImmutable();

        $projectList = [];
        $usersList = [];

        //dodat i exing element gradiliste
        $wellport = 201;

        foreach ($projects as $project) {
            if ($project->getClient()->first()->getId() === $client->getId() || $project->getId() == $wellport) {
                $users = $this->taskRepository->getUsersByDateExing($danas, $project);

                if (!empty($users['lista'])) {
                    $projectList[] = $project->getTitle();
                    $pdf = new \setasign\Fpdi\TcpdfFpdi();

                    // Dodavanje nove stranice
                    $pdf->AddPage();

                    // Putanja do PDF šablona
                    $templatePath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/pdf_template.pdf';

                    // Učitajte šablon
                    $pageCount = $pdf->setSourceFile($templatePath); // Broj stranica u šablonu
                    $templateId = $pdf->importPage(1); // Importuj prvu stranicu iz šablona

                    // Koristite šablon na aktuelnoj stranici
                    $pdf->useTemplate($templateId);
                    $pdf->SetFont('dejavusans', '', 13);

                    $meseci = [
                        1 => 'januar', 2 => 'februar', 3 => 'mart', 4 => 'april',
                        5 => 'maj', 6 => 'jun', 7 => 'jul', 8 => 'avgust',
                        9 => 'septembar', 10 => 'oktobar', 11 => 'novembar', 12 => 'decembar'
                    ];

                    $datum = [
                        'dan' => (new \DateTime())->format('d.'),
                        'mesec' => $meseci[(int)(new \DateTime())->format('m')],
                        'godina' => (new \DateTime())->format('Y.'),
                    ];

                    // Datum
                    $pdf->SetXY(16, 88); // X, Y koordinata gde ide datum
                    $pdf->Write(0, ($datum['mesec']));

                    $pdf->SetXY(62, 88); // X, Y koordinata gde ide datum
                    $pdf->Write(0, ($datum['dan']));

                    $pdf->SetXY(128, 88); // X, Y koordinata gde ide datum
                    $pdf->Write(0, ($datum['godina']));

                    $pdf->SetXY(62, 47);
                    $pdf->Write(0, (mb_strtoupper($project->getTitle())));

                    // Firma
                    $pdf->SetXY(62, 60);
                    $pdf->Write(0, 'PARS DOO BEOGRAD');

                    // Matični broj
                    $pdf->SetXY(62, 70);
                    $pdf->Write(0, '21360031');

                    $startY = 115;
                    foreach ($users['lista'] as $index => $user) {
                        $usersList[] = $user;

                        $pdf->SetXY(33, $startY + ($index * 13));
                        $pdf->Write(0, $user->getIme() . ' ' . $user->getPrezime());

                        $pdf->SetXY(158, $startY + ($index * 13));
                        $pdf->Write(0, $user->getLk());
                    }


                    $pdfPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/exing/dnevni_spisak_' . $project->getId() . '.pdf';

                    // Sačuvajte PDF na zadatoj putanji
                    $pdf->Output($pdfPath, 'F');


                    $sign = new SignEmail();
                    $sign->setProject($project->getId());
                    $sign->setUsers($users['listaSign']);
                    $this->signEmailRepository->save($sign);
                }
            }
        }

        if (is_dir($exingPath)) {
            $files = glob($exingPath . '/*.pdf');
            foreach ($files as $file) {
                $attachments[] = $file;
            }
                        foreach ($usersList as $usr) {
                $atch = $this->em->getRepository(Pdf::class)->findOneBy(['user' => $usr, 'title' => 'Dokumenti']);
                if (!is_null($atch)) {
                    $attachments[] = $this->parameterBag->get('kernel.project_dir') . '/public' . $atch->getPath();
                }
            }
        }


        if (!empty($usersList)) {
            $this->mail->reportsDailyExing('marceta.pars@gmail.com', $client, $danas, $attachments, $projectList);
            $this->mail->reportsDailyExing('zoran.stankovic@exing.co.rs', $client, $danas, $attachments, $projectList);
            $this->mail->reportsDailyExing('radisa.baltic@exing.co.rs', $client, $danas, $attachments, $projectList);
            $this->mail->reportsDailyExing('petar.petrovic@exing.co.rs', $client, $danas, $attachments, $projectList);
            $this->mail->reportsDailyExing('sanja.petronijevic@exing.co.rs', $client, $danas, $attachments, $projectList);
        }

        // Briši fajlove
        foreach (glob($exingPath . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    public function generateAndSendUrbanReports(): void {


        $urbanPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/urban';
        $attachments = [];

        $project = $this->em->getRepository(Project::class)->find(214);
        $danas = new \DateTimeImmutable();

        $projectList = [];
        $usersList = [];

        $users = $this->taskRepository->getUsersByDateExing($danas, $project);

        if (!empty($users['lista'])) {
                    $projectList[] = $project->getTitle();
                    $pdf = new \setasign\Fpdi\TcpdfFpdi();

                    // Dodavanje nove stranice
                    $pdf->AddPage();

                    // Putanja do PDF šablona
                    $templatePath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/pdf_template.pdf';

                    // Učitajte šablon
                    $pageCount = $pdf->setSourceFile($templatePath); // Broj stranica u šablonu
                    $templateId = $pdf->importPage(1); // Importuj prvu stranicu iz šablona

                    // Koristite šablon na aktuelnoj stranici
                    $pdf->useTemplate($templateId);
                    $pdf->SetFont('dejavusans', '', 13);

                    $meseci = [
                        1 => 'januar', 2 => 'februar', 3 => 'mart', 4 => 'april',
                        5 => 'maj', 6 => 'jun', 7 => 'jul', 8 => 'avgust',
                        9 => 'septembar', 10 => 'oktobar', 11 => 'novembar', 12 => 'decembar'
                    ];

                    $datum = [
                        'dan' => (new \DateTime())->format('d.'),
                        'mesec' => $meseci[(int)(new \DateTime())->format('m')],
                        'godina' => (new \DateTime())->format('Y.'),
                    ];

                    // Datum
                    $pdf->SetXY(16, 88); // X, Y koordinata gde ide datum
                    $pdf->Write(0, ($datum['mesec']));

                    $pdf->SetXY(62, 88); // X, Y koordinata gde ide datum
                    $pdf->Write(0, ($datum['dan']));

                    $pdf->SetXY(128, 88); // X, Y koordinata gde ide datum
                    $pdf->Write(0, ($datum['godina']));

                    $pdf->SetXY(62, 47);
                    $pdf->Write(0, (mb_strtoupper($project->getTitle())));

                    // Firma
                    $pdf->SetXY(62, 60);
                    $pdf->Write(0, 'PARS DOO BEOGRAD');

                    // Matični broj
                    $pdf->SetXY(62, 70);
                    $pdf->Write(0, '21360031');

                    $startY = 115;
                    foreach ($users['lista'] as $index => $user) {
                        $usersList[] = $user;

                        $pdf->SetXY(33, $startY + ($index * 13));
                        $pdf->Write(0, $user->getIme() . ' ' . $user->getPrezime());

                        $pdf->SetXY(158, $startY + ($index * 13));
                        $pdf->Write(0, $user->getLk());
                    }


                    $pdfPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/urban/dnevni_spisak_' . $project->getId() . '.pdf';

                    // Sačuvajte PDF na zadatoj putanji
                    $pdf->Output($pdfPath, 'F');


                    $sign = new SignEmail();
                    $sign->setProject($project->getId());
                    $sign->setUsers($users['listaSign']);
                    $this->signEmailRepository->save($sign);
                }

        if (is_dir($urbanPath)) {
            $files = glob($urbanPath . '/*.pdf');
            foreach ($files as $file) {
                $attachments[] = $file;
            }
                        foreach ($usersList as $usr) {
                $atch = $this->em->getRepository(Pdf::class)->findOneBy(['user' => $usr, 'title' => 'Dokumenti']);
                if (!is_null($atch)) {
                    $attachments[] = $this->parameterBag->get('kernel.project_dir') . '/public' . $atch->getPath();
                }
            }
        }


        if (!empty($usersList)) {
            $this->mail->reportsDailyUrban('marceta.pars@gmail.com', $project, $danas, $attachments, $projectList);
        }
        // Briši fajlove
        foreach (glob($urbanPath . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function generatePlatoReports(): void {


        $platoPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/plato';
        $attachments = [];

        $project = $this->em->getRepository(Project::class)->find(152);
        $project1 = $this->em->getRepository(Project::class)->find(247);
        $danas = new \DateTimeImmutable();

        $projectList = [];
        $usersList = [];

        $users = $this->taskRepository->getUsersByDatePlato($danas, $project, $project1);


        if (!empty($users['lista'])) {

            $geoM = 0;
            $geoZ = 0;


            foreach ($users['lista'] as $user) {
                if ($user->getPol() == 1) {
                    $geoM += 1;
                } else {
                    $geoZ += 1;
                }
            }

            $pdf = new \setasign\Fpdi\TcpdfFpdi();

            // Dodavanje nove stranice
            $pdf->AddPage();

            // Putanja do PDF šablona
            $templatePath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/template_plato.pdf';

            // Učitajte šablon
            $pageCount = $pdf->setSourceFile($templatePath); // Broj stranica u šablonu
            $templateId = $pdf->importPage(1); // Importuj prvu stranicu iz šablona

            // Koristite šablon na aktuelnoj stranici
            $pdf->useTemplate($templateId);
            $pdf->SetFont('dejavusans', '', 11);

            $datum = (new \DateTime())->format('d.m.Y.');


            if ($geoM > 0) {// Datum
                $pdf->SetXY(31, 42.5); // X, Y koordinata gde ide datum
                $pdf->Write(0, ($datum));

                $pdf->SetXY(58, 43.5); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Write(0, ('Geodeta / Geodetic Engineer'));

                $pdf->SetXY(98, 43.5); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Write(0, ('Muški / Male'));

                $pdf->SetXY(128, 43.5); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Write(0, ('Lokalni / Local'));

                $pdf->SetXY(165, 42.5); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 11);
                $pdf->Write(0, ($geoM));
            }

            if ($geoZ > 0) {
                $pdf->SetXY(31, 47.5); // X, Y koordinata gde ide datum
                $pdf->Write(0, ($datum));

                $pdf->SetXY(58, 48); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Write(0, ('Geodeta / Geodetic Engineer'));

                $pdf->SetXY(98, 48); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Write(0, ('Ženski / Female'));

                $pdf->SetXY(128, 48); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 7);
                $pdf->Write(0, ('Lokalni / Local'));

                $pdf->SetXY(165, 47); // X, Y koordinata gde ide datum
                $pdf->SetFont('dejavusans', '', 11);
                $pdf->Write(0, ($geoZ));
            }

            $pdf->SetXY(165, 139); // X, Y koordinata gde ide datum
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->Write(0, ($geoZ + $geoM));


            $pdfPath = $this->parameterBag->get('kernel.project_dir') . '/var/pdf/plato/dnevni_spisak_' . $project->getId() . '.pdf';

            // Sačuvajte PDF na zadatoj putanji
            $pdf->Output($pdfPath, 'F');

        }

        if (is_dir($platoPath)) {
            $files = glob($platoPath . '/*.pdf');
            foreach ($files as $file) {
                $attachments[] = $file;
            }
//            foreach ($usersList as $usr) {
//                $atch = $this->em->getRepository(Pdf::class)->findOneBy(['user' => $usr, 'title' => 'Dokumenti']);
//                if (!is_null($atch)) {
//                    $attachments[] = $this->parameterBag->get('kernel.project_dir') . '/public' . $atch->getPath();
//                }
//            }
        }



        $this->mail->reportsDailyPlato('marceta.pars@gmail.com', $project, $danas, $attachments, $projectList, $geoZ+$geoM);
        $this->mail->reportsDailyPlato('marija.mitrovic@alfapreving.rs', $project, $danas, $attachments, $projectList, $geoZ+$geoM);

        // Briši fajlove
        foreach (glob($platoPath . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

}
