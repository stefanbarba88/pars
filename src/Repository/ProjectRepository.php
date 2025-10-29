<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaPlacanjaData;
use App\Classes\Slugify;
use App\Entity\Category;
use App\Entity\Client;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\Pdf;
use App\Entity\Project;
use App\Entity\ProjectHistory;
use App\Entity\StopwatchTime;
use App\Entity\Task;
use App\Entity\TaskLog;
use App\Entity\User;
use DateTimeImmutable;
use DirectoryIterator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository {
  private Security $security;
  public function __construct(ManagerRegistry $registry, Security $security) {
    parent::__construct($registry, Project::class);
    $this->security = $security;
  }

  public function saveProject(Project $project, User $user, ?string $history): Project  {

    $project = $this->paymentTimeSet($project);

    if (!is_null($project->getId())) {

      $historyProject = new ProjectHistory();
      $historyProject->setHistory($history);

      $project->addProjectHistory($historyProject);
      $project->setEditBy($user);

      return $this->save($project);
    }

    return $this->save($project);

  }

  public function suspendProject(Project $project, User $user, ?string $history): Project  {

    if ($project->isSuspended()) {
      $project->setIsSuspended(false);
    } else {
      $project->setIsSuspended(true);
    }
    if (!is_null($project->getId())) {

      $historyProject = new ProjectHistory();
      $historyProject->setHistory($history);

      $project->addProjectHistory($historyProject);
      $project->setEditBy($user);

      return $this->save($project);
    }

    return $this->save($project);

  }
//  public function getProjectsByUser(User $user) {
//    return $this->createQueryBuilder('p')
//      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
//      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
//      ->andWhere('tl.user = :userId')
//      ->setParameter(':userId', $user->getId())
//      ->addOrderBy('p.isSuspended', 'ASC')
//      ->getQuery()
//      ->getResult();
//
//  }
//  public function getAllProjects(): array {
//    return $this->createQueryBuilder('p')
//      ->andWhere('p.isSuspended = 0')
//      ->addOrderBy('p.isSuspended', 'ASC')
//      ->getQuery()
//      ->getResult();
//  }

  public function getAllProjectsPaginator($filter, $suspended) {
    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('p');

      $qb->andWhere('p.isSuspended = :suspended')
      ->andWhere('p.company = :company')
      ->setParameter(':suspended', $suspended)
      ->setParameter(':company', $company);

      if (!empty($filter['title'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('p.title', ':title'),
      ))
        ->setParameter('title', '%' . $filter['title'] . '%');
    }
    if (!empty($filter['tip'])) {
      $qb->andWhere('p.type = :tip');
      $qb->setParameter('tip', $filter['tip']);
    }

    $qb
      ->orderBy('p.noTasks', 'DESC')
      ->addOrderBy('p.title', 'ASC')
      ->getQuery();

    return $qb;
  }

  public function getAllProjectsToVerify($user): \Doctrine\ORM\QueryBuilder {

    $qb = $this->createQueryBuilder('p');

    $qb->andWhere('p.isSuspended = :suspended')
      ->andWhere('p.company = :company')
      ->andWhere('p.isApproved = :approved')
      ->setParameter(':approved', true)
      ->setParameter(':suspended', false)
      ->setParameter(':company', $user->getCompany());

    $qb
      ->orderBy('p.isCreated', 'DESC')
      ->addOrderBy('p.title', 'ASC')
      ->getQuery();

    return $qb;
  }




//  public function getAllProjectsSuspended(): array {
//    return $this->createQueryBuilder('p')
//      ->andWhere('p.isSuspended = 1')
//      ->addOrderBy('p.isSuspended', 'ASC')
//      ->getQuery()
//      ->getResult();
//  }
//
//
//  public function getAllProjectsPermanent(): array {
//
//    $projects = $this->createQueryBuilder('p')
//      ->andWhere('p.isSuspended = 0')
//      ->andWhere('p.type <> 2')
//      ->addOrderBy('p.isSuspended', 'ASC')
//      ->getQuery()
//      ->getResult();
//
//    return $projects;
//  }
//  public function getAllProjectsChange(): array {
//
//    $projects = $this->createQueryBuilder('p')
//      ->andWhere('p.isSuspended = 0')
//      ->andWhere('p.type <> 1')
//      ->addOrderBy('p.isSuspended', 'ASC')
//      ->getQuery()
//      ->getResult();
//
//    return $projects;
//  }

  public function getAllProjectsTypePaginator($filter, $type) {
    $company = $this->security->getUser()->getCompany();

    $qb = $this->createQueryBuilder('p');

    $qb->andWhere('p.isSuspended = 0')
      ->andWhere('p.company = :company')
      ->setParameter(':company', $company)
      ->andWhere('p.type = :tip')
      ->setParameter(':tip', $type);

    if (!empty($filter['title'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('p.title', ':title'),
      ))
        ->setParameter('title', '%' . $filter['title'] . '%');
    }


    $qb
      ->orderBy('p.noTasks', 'DESC')
      ->getQuery();

    return $qb;
  }
  public function getAllProjectsMixPaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('p')
      ->andWhere('p.isSuspended = 0')
      ->andWhere('p.type = 3')
      ->andWhere('p.company = :company')
      ->setParameter(':company', $company)
      ->orderBy('p.noTasks', 'DESC')
      ->getQuery();

  }
  public function getAllProjectsPermanentPaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('p')
      ->andWhere('p.isSuspended = 0')
      ->andWhere('p.type = 1')
      ->andWhere('p.company = :company')
      ->setParameter(':company', $company)
      ->addOrderBy('p.noTasks', 'DESC')
      ->getQuery();

  }
  public function getAllProjectsSuspendedPaginator() {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('p')
      ->andWhere('p.isSuspended = 1')
      ->andWhere('p.company = :company')
      ->setParameter(':company', $company)
      ->orderBy('p.title', 'ASC')
      ->getQuery();
  }
  public function countProjectsChange(): int {
    $company = $this->security->getUser()->getCompany();
    return $this->createQueryBuilder('p')
      ->select('count(p.id)')
      ->andWhere('p.isSuspended = 0 ')
      ->andWhere('p.type <> 1')
      ->andWhere('p.company = :company')
      ->setParameter(':company', $company)
      ->getQuery()
      ->getSingleScalarResult();

  }
//  public function countProjectsPermanent(): int {
//    return $this->createQueryBuilder('p')
//      ->select('count(p.id)')
//      ->andWhere('p.isSuspended = 0 ')
//      ->andWhere('p.type <> 2')
//      ->getQuery()
//      ->getSingleScalarResult();
//
//  }
//  public function countProjectsActive(): int {
//
//    return $this->createQueryBuilder('p')
//      ->select('count(p.id)')
//      ->andWhere('p.isSuspended = 0')
//      ->getQuery()
//      ->getSingleScalarResult();
//
//  }

  public function getImagesByProject(Project $project): array {

    return $this->createQueryBuilder('p')
      ->select('i.thumbnail100', 'i.thumbnail500', 'i.thumbnail1024')
      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Image::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('p.id = :projectId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':projectId', $project->getId())
      ->getQuery()
      ->getResult();

  }
  public function getPdfsByProject(Project $project): array {

    $pdfs = $this->createQueryBuilder('p')
      ->select('i.title', 'i.path', 'i.created')
      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->innerJoin(StopwatchTime::class, 's', Join::WITH, 'tl = s.taskLog')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 's = i.stopwatchTime')
      ->andWhere('p.id = :projectId')
      ->andWhere('s.isDeleted = 0')
      ->setParameter(':projectId', $project->getId())
      ->getQuery()
      ->getResult();


    $pdfsProject = $this->createQueryBuilder('p')
      ->select('i.title', 'i.path', 'i.created')
      ->innerJoin(Pdf::class, 'i', Join::WITH, 'p = i.project')
      ->andWhere('p.id = :projectId')
      ->andWhere('i.stopwatchTime IS NULL')
      ->setParameter(':projectId', $project->getId())
      ->getQuery()
      ->getResult();

    return array_merge($pdfs, $pdfsProject);

  }
  public function save(Project $project): Project {
    if (is_null($project->getId())) {
      $this->getEntityManager()->persist($project);
    }

    $this->getEntityManager()->flush();
    return $project;
  }
  public function remove(Project $entity, bool $flush = false): void {
    $this->getEntityManager()->remove($entity);

    if ($flush) {
      $this->getEntityManager()->flush();
    }
  }
  public function paymentTimeSet(Project $project): Project {

    if ($project->getPayment() == VrstaPlacanjaData::BESPLATNO) {
      $project->setPrice(null);
      $project->setPricePerHour(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::FIKSNA_CENA) {
      $project->setPricePerHour(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_SATU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_DANU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
      $project->setPricePerHour(null);
      $project->setPricePerMonth(null);
    } else if ($project->getPayment() == VrstaPlacanjaData::PLACANJE_PO_MESECU) {
      $project->setPrice(null);
      $project->setPricePerTask(null);
      $project->setPricePerDay(null);
      $project->setPricePerHour(null);
    } else {
      $project->setPricePerHour(null);
      $project->setPrice(null);
      $project->setPricePerDay(null);
      $project->setPricePerMonth(null);
    }

    if (!$project->isTimeRoundUp()) {
      $project->setRoundingInterval(null);
      $project->setMinEntry(null);
    }
    return $project;
  }
  public function findForForm(int $id = 0): Project {

    if (empty($id)) {
      $project = new Project();
      $project->setCreatedBy($this->security->getUser());
      $project->setCompany($this->security->getUser()->getCompany());
      return $project;
    }
    return $this->getEntityManager()->getRepository(Project::class)->find($id);
  }

  public function getReport(array $data): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    $project = $this->getEntityManager()->getRepository(Project::class)->find($data['project']);

    if (isset($data['category'])){
      foreach ($data['category'] as $cat) {
        $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
      }
    } else {
      $kategorija [] = 0;
    }

    $robotika1 = 0;

    if (isset($data['robotika1'])) {
      $robotika1 = 1;
    }


    if (isset($data['naplativ'])) {
      $naplativ = $data['naplativ'];
      return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project, $robotika1, $kategorija, $naplativ);
    }

    return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project, $robotika1, $kategorija);
  }

  public function getReportRuma(array $data): array {
    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    $project1 = $this->getEntityManager()->getRepository(Project::class)->find(94);
    $project2 = $this->getEntityManager()->getRepository(Project::class)->find(130);

    if (isset($data['category'])){
      foreach ($data['category'] as $cat) {
        $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
      }
    } else {
      $kategorija [] = 0;
    }

    $array1 =  $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project1, 0, $kategorija, 1);

    $array2 =  $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project2, 0, $kategorija, 1);

    $result = $array2[0];

// Iteriramo kroz $array1[0]
    foreach ($array1[0] as $key => $value) {
      // Ako ključ iz $array1[0] već postoji u $array2[0], spojimo vrednosti
      if (array_key_exists($key, $result)) {
        $result[$key] = array_merge($result[$key], $value);
      } else {
        // Ako ključ ne postoji u $array2[0], dodajemo novi ključ i vrednost
        $result[$key] = $value;
      }
    }

// Ako želite da ažurirate $array2[0] sa novim podacima
    $array2[0] = $result;
    $tempArray = [];
    foreach ($array2[0] as $dateString => $info) {
      $date = DateTimeImmutable::createFromFormat('d.m.Y.', $dateString);
      if ($date) {
        $tempArray[$date->getTimestamp()] = $info;
      }
    }

// Sortiraj po timestamp-ovima, koji predstavljaju datume
    ksort($tempArray);

// Formatiraj ključeve nazad u originalni format
    $sortedData = [];
    foreach ($tempArray as $timestamp => $info) {
      $date = (new DateTimeImmutable())->setTimestamp($timestamp);
      $formattedDate = $date->format('d.m.Y.') . ' ';
      $sortedData[$formattedDate] = $info;
    }

//    foreach ($sortedData as $datum => &$entries) {
//      $uniqueUsers = [];
//      foreach ($entries as $entry) {
//        foreach ($entry['zaduzeni'] as $zaduzen) {
//          $uniqueUsers[$zaduzen->getId()] = $zaduzen;
//        }
//      }
//      $entries['unique'] = array_values($uniqueUsers);
//    }

    $referentniKorisnici = [50, 57, 96, 52, 60];

    foreach ($sortedData as $datum => &$entries) {
      $uniqueUsers = [];
      $presentUserIds = [];

      foreach ($entries as $entry) {
        foreach ($entry['zaduzeni'] as $zaduzen) {
          $uniqueUsers[$zaduzen->getId()] = $zaduzen;
          $presentUserIds[] = $zaduzen->getId();
        }
      }

      // Dobijanje jedinstvenih ID-ova korisnika koji su prisutni
      $presentUserIds = array_unique($presentUserIds);

      // Dobijanje ID-ova korisnika koji nisu prisutni (odsutni)
      $odsutni = array_diff($referentniKorisnici, $presentUserIds);

      // Dodavanje jedinstvenih korisnika i odsutnih korisnika u entries
      $entries['unique'] = array_values($uniqueUsers);
      $entries['odsutni'] = $odsutni;
    }

  return $sortedData;
  }

  public function getReportAll(array $data): array {
    $company = $this->security->getUser()->getCompany();
    $projectsData = [];

    $dates = explode(' - ', $data['period']);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    if (isset($data['category'])){
      foreach ($data['category'] as $cat) {
        $kategorija [] = $this->getEntityManager()->getRepository(Category::class)->findOneBy(['id' => $cat]);
      }
    } else {
      $kategorija [] = 0;
    }

    $projects = $this->getEntityManager()->getRepository(Project::class)->findBy(['isSuspended' => false, 'company' => $company], ['title' => 'ASC']);

    foreach ($projects as $project) {

      if (isset($data['naplativ'])) {
        $naplativ = $data['naplativ'];
        $rep = $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project, $kategorija, $naplativ);
        if (!empty ($rep[0])) {
          $projectsData[] = $rep;
        }
      } else {
        $rep =  $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProject($start, $stop, $project, $kategorija);
        if (!empty ($rep[0])) {
          $projectsData[] = $rep;
        }
      }

    }

    return $projectsData;

  }


  public function countClientTasks(Company $company, Client $client, Category $category, DateTimeImmutable $prethodniMesecDatum, DateTimeImmutable $danas) : array {

    $projectsByClient = [];

    $projects = $this->getEntityManager()->getRepository(Project::class)->findBy(['isSuspended' => false, 'company' => $company]);
    $totalTasks = 0;

    foreach ($projects as $project) {
      if ($project->getClient()->first()->getId() == $client->getId()) {
        $noTasks = $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProjectCommand($prethodniMesecDatum, $danas, $project, $category);
        $projectsByClient[] = [
          'project' => $project,
          'tasks' => $noTasks
        ];
        $totalTasks = $totalTasks + $noTasks;
      }
    }

    return [$projectsByClient, $totalTasks];
  }

  public function getCountTasksByProject(Project $project):array {

    $category = $this->getEntityManager()->getRepository(Category::class)->find(5);
    $prethodniMesecDatum = new DateTimeImmutable('first day of this month');

    $datum = new DateTimeImmutable();
    $datum = $datum->setTime(23,59);

    if ($project->getClient()->first()->getId() == 5) {
      if ($datum->format('j') < 27) {
        $prethodniMesecDatum = new DateTimeImmutable('last day of last month');

      }
      $prethodniMesecDatum = $prethodniMesecDatum->setDate($prethodniMesecDatum->format('Y'), $prethodniMesecDatum->format('m'), 26);
    }


    $prethodniMesecDatum = $prethodniMesecDatum->setTime(0,0);


    return $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProjectAllCategory($prethodniMesecDatum, $datum, $project, $category);

  }

  // A/B/C/D
  // A - broj terenskih dana
  // B - broj kancelarijskih dana ali da nije bilo terena taj dan
  // C - trenutni broj radnih dana u mesecu
  // D - ukupan broj radnih dana u mesecu
  public function getCountDaysTasksByProject(Project $project):array {

    $teren = $this->getEntityManager()->getRepository(Category::class)->find(5);
    $kancelarija = $this->getEntityManager()->getRepository(Category::class)->find(6);


    $datum = new DateTimeImmutable();
    $datum = $datum->setTime(23,59);

    $brojDana = date('j');

    $startDate = new DateTimeImmutable('first day of this month');
    if ($project->getClient()->first()->getId() == 5) {
      if ($datum->format('j') < 27) {
        $startDate = new DateTimeImmutable('last day of last month');
      }
      $startDate = $startDate->setDate($startDate->format('Y'), $startDate->format('m'), 26);
      $brojDana = $startDate->format('t');
    }



    $brojTeren = 0;
    $brojKancelarija = 0;

    for ($i = 0; $i < $brojDana; $i++) {
      $task = $this->getEntityManager()->getRepository(Task::class)->proveriPoKat($startDate, $project, $teren);
      if ($task > 0) {
        $brojTeren++;
      } else {
        $task = $this->getEntityManager()->getRepository(Task::class)->proveriPoKat($startDate, $project, $kancelarija);
        if ($task > 0) {
          $brojKancelarija++;
        }
      }
      $startDate = $startDate->modify("+1 day");

    }

    return [
      'teren' => $brojTeren,
      'kancelarija' =>$brojKancelarija
    ];

  }

  public function getCountTasksByProjectCompany(Company $company, $data):array {

    $projects = $this->getEntityManager()->getRepository(Project::class)->findBy(['company' => $company, 'isSuspended' => false], ['title' => 'ASC']);

    $datum = $data['report_form']['period'];
    $dates = explode(' - ', $datum);

    $category = [];

    if (isset($data['report_form']['category'])) {
      foreach ($data['report_form']['category'] as $cat) {
        if ($cat != '') {
          $category[] = $this->getEntityManager()->getRepository(Category::class)->find($cat);
        }
      }
    }

    if (empty($category)) {
      $category1 = $this->getEntityManager()->getRepository(Category::class)->findBy(['isSuspended' => false, 'company' => $company], ['id' => 'ASC']);
      $category2 = $this->getEntityManager()->getRepository(Category::class)->findBy(['isSuspended' => false, 'company' => NULL], ['id' => 'ASC']);
      $category = array_merge($category1, $category2);
    }

    usort($category, function ($a, $b) {
      return $a->getId() - $b->getId();
    });

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);


    $reports = [];

//    $project = $this->getEntityManager()->getRepository(Project::class)->find(25);
    foreach ($projects as $project) {
      $reports[] = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProjectAllCategoryReport($start, $stop, $project, $category);
    }
    usort($reports, function ($a, $b) {
      return $b['total'] - $a['total'];
    });

    return [$reports, $category];

  }

  public function getAllProjectsByNoTasks(Company $company) {

   return $this->createQueryBuilder('p')
      ->andWhere('p.company = :company')
      ->andWhere('p.noTasks > 0')
      ->setParameter(':company', $company)
      ->getQuery()
      ->getResult();

  }

  public function processMonthlyTasks(Project $project): array {

    $zadaci = [];
    $category = $this->getEntityManager()->getRepository(Category::class)->find(5);
    $currentYear = date('Y');
    $currentMonth = date('m');

    for ($month = 1; $month <= $currentMonth; $month++) {
      $startOfMonth = new DateTimeImmutable("$currentYear-$month-01");
      $endOfMonth = new DateTimeImmutable("$currentYear-$month-" . date('t', strtotime("$currentYear-$month-01")));
      $tasks = $this->getEntityManager()->getRepository(Task::class)->getTasksByDateAndProjectAllCategory($startOfMonth, $endOfMonth, $project, $category);
      $zadaci[] = $tasks;
    }

    $sviZadaci = [];
    $teren = [];

    foreach ($zadaci as $zadatak) {
      $sviZadaci[] = $zadatak['total'];
      $teren[] = $zadatak['teren'];
    }

    return [$sviZadaci, $teren];

  }



  public function getReportXls(string $datum, Project $projekat): array {

    $dates = explode(' - ', $datum);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProjectXls($start, $stop, $projekat);
  }
  public function getReportXlsRobotika(string $datum, Project $projekat): array {

    $dates = explode(' - ', $datum);

    $start = DateTimeImmutable::createFromFormat('d.m.Y', $dates[0]);
    $stop = DateTimeImmutable::createFromFormat('d.m.Y', $dates[1]);

    return $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProjectXlsRobots($start, $stop, $projekat);
  }

  public function getProjectsSearchByUserPaginator(User $user, $filterBy) {
    $keywords = explode(" ", $filterBy['tekst']);

    $qb = $this->createQueryBuilder('p')
      ->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->andWhere('tl.user = :userId')
      ->andWhere('p.isSuspended = 0')
      ->andWhere('p.company = :company')
      ->setParameter(':userId', $user->getId())
      ->setParameter(':company', $user->getCompany());

        foreach ($keywords as $key => $keyword) {
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->like('p.title', ':keyword'.$key),
        ))
        ->setParameter('keyword'.$key, '%' . $keyword . '%');
      }

      $qb->addOrderBy('p.title', 'ASC');
      $qb->getQuery();

    return $qb;
  }

  public function getProjectsByUserPaginator(User $user, $filter) {

    $qb = $this->createQueryBuilder('p');

      $qb->innerJoin(Task::class, 't', Join::WITH, 'p = t.project')
      ->innerJoin(TaskLog::class, 'tl', Join::WITH, 't = tl.task')
      ->andWhere('tl.user = :userId')
      ->andWhere('p.isSuspended = 0')
      ->setParameter(':userId', $user->getId());

    if (!empty($filter['title'])) {
      $qb->andWhere($qb->expr()->orX(
        $qb->expr()->like('p.title', ':title'),
      ))
        ->setParameter('title', '%' . $filter['title'] . '%');
    }
    if (!empty($filter['tip'])) {
      $qb->andWhere('p.type = :tip');
      $qb->setParameter('tip', $filter['tip']);
    }

      $qb->addOrderBy('p.title', 'ASC')
      ->getQuery();

    return $qb;
  }

  public function getProjectsSearchPaginator($filterBy, User $user){
    $company = $user->getCompany();
    $qb = $this->createQueryBuilder('t');

    $qb->where('t.company = :company');
    $qb->setParameter(':company', $company);


    if ($filterBy['status'] == 1) {
      $qb->andWhere('t.isSuspended = :status');
      $qb->setParameter('status', $filterBy['statusStanje']);
    } else {
      $qb->andWhere('t.isSuspended <> :status');
      $qb->setParameter('status', $filterBy['statusStanje']);
    }

    $keywords = explode(" ", $filterBy['tekst']);

    foreach ($keywords as $key => $keyword) {
      $qb
        ->andWhere($qb->expr()->orX(
          $qb->expr()->like('t.title', ':keyword'.$key),
        ))
        ->setParameter('keyword'.$key, '%' . $keyword . '%');
    }

    $qb
      ->addOrderBy('t.title', 'ASC')
      ->getQuery();


    return $qb;
  }

  public function getReportsGenerator(Company $company, Client $client, DateTimeImmutable $prethodniMesecDatum, DateTimeImmutable $datum, string $excelDir, DateTimeImmutable $danas) : array {

    $projectsByClient = [];
    $projectsByClientReal = [];

    $projects = $this->getEntityManager()->getRepository(Project::class)->findBy(['isSuspended' => false, 'company' => $company]);

    foreach ($projects as $project) {
      if ($project->getClient()->first()->getId() == $client->getId()) {
        $projectsByClient []= $project;
      }
    }

    if (!is_dir($excelDir)) {
      mkdir($excelDir, 0777, true);
    }

    foreach ($projectsByClient as $projekat) {

      $report = $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProjectXls($prethodniMesecDatum, $datum, $projekat);

      $klijent = $projekat->getClientsJson();
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
      $sheet->getPageSetup()->setFitToWidth(1);
      $sheet->getPageSetup()->setFitToHeight(0);
      $sheet->getPageMargins()->setTop(1);
      $sheet->getPageMargins()->setRight(0.75);
      $sheet->getPageMargins()->setLeft(0.75);
      $sheet->getPageMargins()->setBottom(1);
      $styleArray = [
        'borders' => [
          'outline' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['argb' => '000000'],
          ],
        ],
      ];

      if (!empty ($report)) {

        $projectsByClientReal[] = $projekat->getTitle();

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setWidth(45);
        $sheet->getColumnDimension('J')->setAutoSize(true);


        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('A1', $klijent[0] . ': ' . $projekat->getTitle() . ' - ' . $prethodniMesecDatum->format('d.m.Y') . ' - ' . $datum->format('d.m.Y'));
        $style = $sheet->getStyle('A1:J1');
        $font = $style->getFont();
        $font->setSize(18); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran

        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:I2');
        $sheet->mergeCells('J2:J3');

        $sheet->setCellValue('A2', 'Datum');
        $sheet->setCellValue('B2', 'Opis izvedenog posla');
        $sheet->setCellValue('I2', 'Izvršioci');


        $sheet->setCellValue('B3', 'Aktivnosti');
        $sheet->setCellValue('C3', 'Klijent*');
        $sheet->setCellValue('D3', 'Obrada podataka');
        $sheet->setCellValue('E3', 'Start');
        $sheet->setCellValue('F3', 'Kraj');
        $sheet->setCellValue('G3', 'Razlika');
        $sheet->setCellValue('H3', 'Ukupno');
        $sheet->setCellValue('I3', 'Napomena');

        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:I2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('J2:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J2:J3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $font = $sheet->getStyle('A')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('B')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('C')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('D')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('E')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('F')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('G')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('H')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('I')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14


        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $dani = [];

        $start = 4;
        $start1 = 4;
        $rows = [];
        foreach ($report[1] as $item) {
          if ($item != 1) {
            $offset = $item - 1;
            $sheet->mergeCells('A' . $start . ':A' . $start + $offset);
            $sheet->mergeCells('H' . $start . ':H' . $start + $offset);
//          $sheet->mergeCells('H' . $start . ':H' . $start + $offset);
//          $sheet->mergeCells('I' . $start . ':I' . $start + $offset);
          }
          $rows[] = $start;
          $start = $start + $item;
        }
        $row = 0;
        $row1 = 0;
        $startAktivnosti = 4;


        foreach ($report[2] as $key => $item) {
          $start1 = $rows[$row1];

          $sheet->setCellValue('H' . $start1, $item['vreme']);
          $sheet->getStyle('H' . $start1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('H' . $start1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row1++;
        }

        foreach ($report[0] as $key => $item) {

          $dan = '';

          if ($item[0]['dan'] == 1) {
            $dan = '(Praznik)';
            $dani[] = $row;
          }
          if ($item[0]['dan'] == 3) {
            $dan = '(Nedelja)';
            $dani[] = $row;
          }
          if ($item[0]['dan'] == 5) {
            $dan = '(Praznik i nedelja)';
            $dani[] = $row;
          }

          $start = $rows[$row];

          if (empty($dan)) {
            $sheet->setCellValue('A' . $start, $key);
          } else {
            $sheet->setCellValue('A' . $start, $key . "\n" . $dan);
          }
          $sheet->getStyle('A' . $start)->getAlignment()->setWrapText(true);
          $sheet->getStyle('A' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('A' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row++;
        }

        $row = 0;
        foreach ($report[3] as $item) {

          if (in_array($row, $dani)) {
            $dan = true;
          } else {
            $dan = false;
          }

          foreach ($item as $stopwatch) {

            if ($dan) {
              $range = 'A' . $startAktivnosti . ':J' . $startAktivnosti;
              $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID);
              $sheet->getStyle($range)->getFill()->getStartColor()->setARGB('FFC0C0C0');
            }

            $aktivnosti = [];
            foreach ($stopwatch['activity'] as $akt) {
              if ($akt->getId() != constant('App\\Classes\\AppConfig::NEMA_U_LISTI_ID') && $akt->getId() != constant('App\\Classes\\AppConfig::OSTALO_ID')) {
                $aktivnosti [] = $akt->getTitle();
              }
            }

            $recenice = array_map('trim', preg_split('/[.!?]+/', $stopwatch['additionalActivity'], -1, PREG_SPLIT_NO_EMPTY));
            $sveAktivnosti = array_merge($aktivnosti, $recenice);

            $combinedActivities = implode("\n", $sveAktivnosti);

            $sheet->setCellValue('B' . $startAktivnosti, $combinedActivities);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('D' . $startAktivnosti, $stopwatch['additionalDesc']);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('E' . $startAktivnosti, $stopwatch['start']->format('H:i'));
            $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


            $sheet->setCellValue('F' . $startAktivnosti, $stopwatch['stop']->format('H:i'));
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('G' . $startAktivnosti, $stopwatch['hours'] . ':' . $stopwatch['minutes']);
            $sheet->getStyle('G' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if ($dan) {
              $sheet->setCellValue('I' . $startAktivnosti, $stopwatch['description'] . "\n" . '(PRAZNIK)');
            } else {
              $sheet->setCellValue('I' . $startAktivnosti, $stopwatch['description']);
            }
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $users = '';
            $usersCount = count($stopwatch['users']);

            foreach ($stopwatch['users'] as $key => $user) {
              $users .= $user->getFullName();

              // Ako nije poslednji član u nizu, dodaj "\n"
              if ($key !== $usersCount - 1) {
                $users .= "\n";
              }
            }

            $sheet->setCellValue('J' . $startAktivnosti, $users);
            $sheet->getStyle('J' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('J' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('J' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if (!is_null($stopwatch['client'])) {
              $sheet->setCellValue('C' . $startAktivnosti, $stopwatch['client']->getTitle());
              $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            $startAktivnosti++;
          }
          $row++;
        }
        $dimension = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:J3')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A1:J3')->getFill()->getStartColor()->setRGB('CCCCCC');

        // Postavite font za opseg od A1 do M2
        $style = $sheet->getStyle('A2:J3');
        $font = $style->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran
//      $sheet->getStyle('A4:M14')->applyFromArray($styleArray);
//      $sheet->getStyle('A15:M16')->applyFromArray($styleArray);
        $start = 4;
        foreach ($report[1] as $item) {
//        dd($item);
          $offset = $item - 1;
          $offset = $offset + $start;
//        dd($offset);

          $sheet->getStyle('A' . $start . ':J' . $offset)->applyFromArray($styleArray);

          $start = $offset + 1;

        }

//      $dimension = $sheet->calculateWorksheetDimension();
//      $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
//      $sheet->getStyle('A1:I3')->getFill()->setFillType(Fill::FILL_SOLID);
//      $sheet->getStyle('A1:I3')->getFill()->getStartColor()->setRGB('CCCCCC');
//
//
//      $style = $sheet->getStyle('A2:I3');
//      $font = $style->getFont();
//
//      $font->setSize(14);
//      $font->setBold(true);
//
        $sheet->setCellValue('B' . $startAktivnosti + 1, 'Datum: ' . $datum->format('d.m.Y'));
        if ($client->getId() == 5) {
          $sheet->setCellValue('B' . $startAktivnosti + 1, 'Datum: ' . $danas->format('d.m.Y'));
        }

        $sheet->setCellValue('B' . $startAktivnosti + 1, 'Datum: ' . $datum->format('d.m.Y'));

        $sheet->getStyle('B' . $startAktivnosti + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B' . $startAktivnosti + 1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('B' . $startAktivnosti + 5, 'Za ' . $klijent[0] . ':');

        $sheet->getStyle('B' . $startAktivnosti + 6)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $startAktivnosti + 6)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells('B' . $startAktivnosti + 6 . ':B' . $startAktivnosti + 12);

        $sheet->getStyle('B' . $startAktivnosti + 12)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

//        $sheet->mergeCells('F' . $startAktivnosti + 6 . ':H' . $startAktivnosti + 6);
        $sheet->mergeCells('F' . $startAktivnosti + 6 . ':H' . $startAktivnosti + 12);
        $sheet->setCellValue('F' . $startAktivnosti + 5, 'Za PARS DOO:');

        $sheet->getStyle('F' . $startAktivnosti + 5)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . $startAktivnosti + 5)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('F' . $startAktivnosti + 12 . ':H' . $startAktivnosti + 12)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);


        $sheet->setTitle("Izvestaj");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xls($spreadsheet);

        // In this case, we want to write the file in the public directory
        $publicDirectory = $excelDir;
        // e.g /var/www/project/public/my_first_excel_symfony4.xlsx

        $naziv = Slugify::slugify($projekat->getTitle() . '_'. $datum->format('d.m.Y') );

        $excelFilepath =  $publicDirectory . '/'. $naziv.'.xls';

        // Create the file
        try {
          $writer->save($excelFilepath);
        } catch (Exception $e) {
          dd( 'Caught exception: ',  $e->getMessage(), "\n");
        }

      }


//
//          // Omogućite preuzimanje na strani korisnika
//          header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
//          header('Content-Disposition: attachment;filename="'.$slugify->slugify($projekat->getTitle(), '_') . '_'. $slugify->slugify($datum, '_') . '.xls"');
//
//// Čitanje fajla i slanje na izlaz
//          readfile($excelFilepath);
//
//// Obrišite fajl nakon slanja
//          unlink($excelFilepath);
//dd($pro);
    }


//    $files = new DirectoryIterator($excelDir);
//    $excelFiles = [];
//    $filesPath = [];
//
//    foreach ($files as $file) {
//      if ($file->isFile()) {
//        $filePath = $file->getPathname();
//        $fileName = $file->getFilename();
//        $excelFiles[] = [
//          'name' => $fileName,
//          'path' => $filePath,
//        ];
//        $filesPath[] = $fileName;
//      }
//    }
//
//    $args['files'] = $excelFiles;
//    $args['filesPath'] = $filesPath;
return $projectsByClientReal;

  }
  public function getReportsGeneratorExpo(array $expo, DateTimeImmutable $prethodniMesecDatum, DateTimeImmutable $datum, string $excelDir, DateTimeImmutable $danas) : array {

    $projectsByClient = [];
    $projectsByClientReal = [];

    $projectsByClient[] = $this->getEntityManager()->getRepository(Project::class)->find($expo[0]);
    $projectsByClient[] = $this->getEntityManager()->getRepository(Project::class)->find($expo[1]);


    if (!is_dir($excelDir)) {
      mkdir($excelDir, 0777, true);
    }

    foreach ($projectsByClient as $projekat) {

      $report = $this->getEntityManager()->getRepository(StopwatchTime::class)->getStopwatchesByProjectXls($prethodniMesecDatum, $datum, $projekat);

      $klijent = $projekat->getClientsJson();
      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
      $sheet->getPageSetup()->setFitToWidth(1);
      $sheet->getPageSetup()->setFitToHeight(0);
      $sheet->getPageMargins()->setTop(1);
      $sheet->getPageMargins()->setRight(0.75);
      $sheet->getPageMargins()->setLeft(0.75);
      $sheet->getPageMargins()->setBottom(1);
      $styleArray = [
        'borders' => [
          'outline' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['argb' => '000000'],
          ],
        ],
      ];

      if (!empty ($report)) {

        $projectsByClientReal[] = $projekat->getTitle();

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setWidth(45);
        $sheet->getColumnDimension('J')->setAutoSize(true);


        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->setCellValue('A1', $klijent[0] . ': ' . $projekat->getTitle() . ' - ' . $prethodniMesecDatum->format('d.m.Y') . ' - ' . $datum->format('d.m.Y'));
        $style = $sheet->getStyle('A1:J1');
        $font = $style->getFont();
        $font->setSize(18); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran

        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:I2');
        $sheet->mergeCells('J2:J3');

        $sheet->setCellValue('A2', 'Datum');
        $sheet->setCellValue('B2', 'Opis izvedenog posla');
        $sheet->setCellValue('I2', 'Izvršioci');


        $sheet->setCellValue('B3', 'Aktivnosti');
        $sheet->setCellValue('C3', 'Klijent*');
        $sheet->setCellValue('D3', 'Obrada podataka');
        $sheet->setCellValue('E3', 'Start');
        $sheet->setCellValue('F3', 'Kraj');
        $sheet->setCellValue('G3', 'Razlika');
        $sheet->setCellValue('H3', 'Ukupno');
        $sheet->setCellValue('I3', 'Napomena');

        $sheet->getStyle('A2:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('B2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B2:I2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('J2:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J2:J3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $font = $sheet->getStyle('A')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('B')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('C')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('D')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('E')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('F')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('G')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('H')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font = $sheet->getStyle('I')->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14


        $sheet->getStyle('B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('F3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('I3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $dani = [];

        $start = 4;
        $start1 = 4;
        $rows = [];
        foreach ($report[1] as $item) {
          if ($item != 1) {
            $offset = $item - 1;
            $sheet->mergeCells('A' . $start . ':A' . $start + $offset);
            $sheet->mergeCells('H' . $start . ':H' . $start + $offset);
//          $sheet->mergeCells('H' . $start . ':H' . $start + $offset);
//          $sheet->mergeCells('I' . $start . ':I' . $start + $offset);
          }
          $rows[] = $start;
          $start = $start + $item;
        }
        $row = 0;
        $row1 = 0;
        $startAktivnosti = 4;


        foreach ($report[2] as $key => $item) {
          $start1 = $rows[$row1];

          $sheet->setCellValue('H' . $start1, $item['vreme']);
          $sheet->getStyle('H' . $start1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('H' . $start1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row1++;
        }

        foreach ($report[0] as $key => $item) {

          $dan = '';

          if ($item[0]['dan'] == 1) {
            $dan = '(Praznik)';
            $dani[] = $row;
          }
          if ($item[0]['dan'] == 3) {
            $dan = '(Nedelja)';
            $dani[] = $row;
          }
          if ($item[0]['dan'] == 5) {
            $dan = '(Praznik i nedelja)';
            $dani[] = $row;
          }

          $start = $rows[$row];

          if (empty($dan)) {
            $sheet->setCellValue('A' . $start, $key);
          } else {
            $sheet->setCellValue('A' . $start, $key . "\n" . $dan);
          }
          $sheet->getStyle('A' . $start)->getAlignment()->setWrapText(true);
          $sheet->getStyle('A' . $start)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('A' . $start)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
          $row++;
        }

        $row = 0;
        foreach ($report[3] as $item) {

          if (in_array($row, $dani)) {
            $dan = true;
          } else {
            $dan = false;
          }

          foreach ($item as $stopwatch) {

            if ($dan) {
              $range = 'A' . $startAktivnosti . ':J' . $startAktivnosti;
              $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID);
              $sheet->getStyle($range)->getFill()->getStartColor()->setARGB('FFC0C0C0');
            }

            $aktivnosti = [];
            foreach ($stopwatch['activity'] as $akt) {
              if ($akt->getId() != constant('App\\Classes\\AppConfig::NEMA_U_LISTI_ID') && $akt->getId() != constant('App\\Classes\\AppConfig::OSTALO_ID')) {
                $aktivnosti [] = $akt->getTitle();
              }
            }

            $recenice = array_map('trim', preg_split('/[.!?]+/', $stopwatch['additionalActivity'], -1, PREG_SPLIT_NO_EMPTY));
            $sveAktivnosti = array_merge($aktivnosti, $recenice);

            $combinedActivities = implode("\n", $sveAktivnosti);

            $sheet->setCellValue('B' . $startAktivnosti, $combinedActivities);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('B' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('D' . $startAktivnosti, $stopwatch['additionalDesc']);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('E' . $startAktivnosti, $stopwatch['start']->format('H:i'));
            $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


            $sheet->setCellValue('F' . $startAktivnosti, $stopwatch['stop']->format('H:i'));
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('G' . $startAktivnosti, $stopwatch['hours'] . ':' . $stopwatch['minutes']);
            $sheet->getStyle('G' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if ($dan) {
              $sheet->setCellValue('I' . $startAktivnosti, $stopwatch['description'] . "\n" . '(PRAZNIK)');
            } else {
              $sheet->setCellValue('I' . $startAktivnosti, $stopwatch['description']);
            }
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('I' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $users = '';
            $usersCount = count($stopwatch['users']);

            foreach ($stopwatch['users'] as $key => $user) {
              $users .= $user->getFullName();

              // Ako nije poslednji član u nizu, dodaj "\n"
              if ($key !== $usersCount - 1) {
                $users .= "\n";
              }
            }

            $sheet->setCellValue('J' . $startAktivnosti, $users);
            $sheet->getStyle('J' . $startAktivnosti)->getAlignment()->setWrapText(true);
            $sheet->getStyle('J' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('J' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            if (!is_null($stopwatch['client'])) {
              $sheet->setCellValue('C' . $startAktivnosti, $stopwatch['client']->getTitle());
              $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
              $sheet->getStyle('C' . $startAktivnosti)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            }

            $startAktivnosti++;
          }
          $row++;
        }
        $dimension = $sheet->calculateWorksheetDimension();
        $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:J3')->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle('A1:J3')->getFill()->getStartColor()->setRGB('CCCCCC');

        // Postavite font za opseg od A1 do M2
        $style = $sheet->getStyle('A2:J3');
        $font = $style->getFont();
        $font->setSize(14); // Postavite veličinu fonta na 14
        $font->setBold(true); // Postavite font kao boldiran
//      $sheet->getStyle('A4:M14')->applyFromArray($styleArray);
//      $sheet->getStyle('A15:M16')->applyFromArray($styleArray);
        $start = 4;
        foreach ($report[1] as $item) {
//        dd($item);
          $offset = $item - 1;
          $offset = $offset + $start;
//        dd($offset);

          $sheet->getStyle('A' . $start . ':J' . $offset)->applyFromArray($styleArray);

          $start = $offset + 1;

        }

//      $dimension = $sheet->calculateWorksheetDimension();
//      $sheet->getStyle($dimension)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
//      $sheet->getStyle('A1:I3')->getFill()->setFillType(Fill::FILL_SOLID);
//      $sheet->getStyle('A1:I3')->getFill()->getStartColor()->setRGB('CCCCCC');
//
//
//      $style = $sheet->getStyle('A2:I3');
//      $font = $style->getFont();
//
//      $font->setSize(14);
//      $font->setBold(true);
//
        $sheet->setCellValue('B' . $startAktivnosti + 1, 'Datum: ' . $datum->format('d.m.Y'));

        $sheet->getStyle('B' . $startAktivnosti + 1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('B' . $startAktivnosti + 1)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue('B' . $startAktivnosti + 5, 'Za ' . $klijent[0] . ':');

        $sheet->getStyle('B' . $startAktivnosti + 6)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $startAktivnosti + 6)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->mergeCells('B' . $startAktivnosti + 6 . ':B' . $startAktivnosti + 12);

        $sheet->getStyle('B' . $startAktivnosti + 12)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

//        $sheet->mergeCells('F' . $startAktivnosti + 6 . ':H' . $startAktivnosti + 6);
        $sheet->mergeCells('F' . $startAktivnosti + 6 . ':H' . $startAktivnosti + 12);
        $sheet->setCellValue('F' . $startAktivnosti + 5, 'Za PARS DOO:');

        $sheet->getStyle('F' . $startAktivnosti + 5)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . $startAktivnosti + 5)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('F' . $startAktivnosti + 12 . ':H' . $startAktivnosti + 12)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);


        $sheet->setTitle("Izvestaj");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xls($spreadsheet);

        // In this case, we want to write the file in the public directory
        $publicDirectory = $excelDir;
        // e.g /var/www/project/public/my_first_excel_symfony4.xlsx

        $naziv = Slugify::slugify($projekat->getTitle() . '_'. $datum->format('d.m.Y') );

        $excelFilepath =  $publicDirectory . '/'. $naziv.'.xls';

        // Create the file
        try {
          $writer->save($excelFilepath);
        } catch (Exception $e) {
          dd( 'Caught exception: ',  $e->getMessage(), "\n");
        }

      }


//
//          // Omogućite preuzimanje na strani korisnika
//          header('Content-Type: application/openxmlformats-officedocument.spreadsheetml.sheet');
//          header('Content-Disposition: attachment;filename="'.$slugify->slugify($projekat->getTitle(), '_') . '_'. $slugify->slugify($datum, '_') . '.xls"');
//
//// Čitanje fajla i slanje na izlaz
//          readfile($excelFilepath);
//
//// Obrišite fajl nakon slanja
//          unlink($excelFilepath);
//dd($pro);
    }

    return $projectsByClientReal;

  }

}
