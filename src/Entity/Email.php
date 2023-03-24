<?php

namespace App\Entity;

use App\Repository\EmailRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'emails')]
class Email {
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $toEmail = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $subject = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $body = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $file = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $function = null;

  #[ORM\Column(nullable: true)]
  private ?int $line = null;

  #[ORM\Column]
  private DateTimeImmutable $created;


  public function __construct() {
  }

  public static function create(string $toEmail, string $subject, string $body, string $file, string $function, int $line): Email {
    $mail = new self();
    $mail->setToEmail($toEmail);
    $mail->setSubject($subject);
    $mail->setBody($body);
    $mail->setFile($file);
    $mail->setFunction($function);
    $mail->setLine($line);

    return  $mail;
  }

  #[ORM\PrePersist]
  public function prePersist(): void {
    $this->created = new DateTimeImmutable();
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getToEmail(): ?string {
    return $this->toEmail;
  }

  public function setToEmail(?string $toEmail): self {
    $this->toEmail = $toEmail;

    return $this;
  }

  public function getSubject(): ?string {
    return $this->subject;
  }

  public function setSubject(?string $subject): self {
    $this->subject = $subject;

    return $this;
  }

  public function getBody(): ?string {
    return $this->body;
  }

  public function setBody(?string $body): self {
    $this->body = $body;

    return $this;
  }

  public function getFile(): ?string {
    return $this->file;
  }

  public function setFile(?string $file): self {
    $this->file = $file;

    return $this;
  }

  public function getFunction(): ?string {
    return $this->function;
  }

  public function setFunction(?string $function): self {
    $this->function = $function;

    return $this;
  }

  public function getLine(): ?int {
    return $this->line;
  }

  public function setLine(?int $line): self {
    $this->line = $line;

    return $this;
  }

  /**
   * @return DateTimeImmutable
   */
  public function getCreated(): DateTimeImmutable {
    return $this->created;
  }

  /**
   * @param DateTimeImmutable $created
   */
  public function setCreated(DateTimeImmutable $created): void {
    $this->created = $created;
  }


}
