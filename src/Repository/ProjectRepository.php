<?php

namespace App\Repository;

use App\Classes\Data\UserRolesData;
use App\Classes\Data\VrstaPlacanjaData;
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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
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


//    /**
//     * @return Project[] Returns an array of Project objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
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


}
