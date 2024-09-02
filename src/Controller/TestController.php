<?php

namespace App\Controller;

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

use App\Service\MailService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/email')]
class TestController extends AbstractController {
  public function __construct(private readonly ManagerRegistry $em) {
  }

  #[Route('/send-email/', name: 'send_email')]
  public function sendEmail(MailService $mailService): Response {

    $mailService->test('stefanmaksimovic88@hotmail.com');
    $mailService->test('stefanmaksimovic88@gmail.com');
    $mailService->test('sm@epars.rs');

    return $this->redirectToRoute('app_home');
  }

  #[Route('/qr-code/', name: 'generate_qr')]
  public function generateQrCode(): Response {
    // Kreiranje QR koda
    $qrCode = new QrCode('https://epars.rs');
    $qrCode->setSize(800);
    $qrCode->setMargin(10);
    $qrCode->setEncoding(new Encoding('UTF-8'));
    $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::Low);

    // Kreiranje SVG pisca
    $writer = new SvgWriter();
    $result = $writer->write($qrCode);
    $svgContent = $result->getString(); // Pretvaranje rezultata u string

    // Kreiranje odgovora sa SVG sadržajem
    return new Response($svgContent, 200, [
      'Content-Type' => 'image/svg+xml',
    ]);

  }

}
