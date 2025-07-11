<?php

namespace App\Classes\Data;
class NotifyMessagesData {

  public const REGISTRATION_USER_SUCCESS = 'Uspešno ste kreirali novog korisnika.';
  public const EDIT_USER_SUCCESS = 'Uspešno ste izmenili korisnički profil.';
  public const EDIT_USER_IMAGE_SUCCESS = 'Uspešno ste izmenili profilnu sliku korisnika.';
  public const USER_SUSPENDED_TRUE = 'Uspešno ste deaktivirali nalog.';
  public const USER_SUSPENDED_FALSE = 'Uspešno ste aktivirali nalog.';
  public const USER_SUSPENDED_QUESTION = 'Da li zaista želite da deaktivirate nalog?';


  public const DELETE_ERROR_PROJEKAT = 'Nije moguće obrisati stavku, potrebno je predhodno obrisati sve stavke koje se nalaze u njoj.';


  public const EDIT_SUCCESS = 'Uspešno ste kreirali/izmenili stavku.';
  public const DELETE_SUCCESS = 'Uspešno ste obrisali stavku.';

  //Poruke za klijenta
  public const CLIENT_ADD = 'Uspešno ste kreirali/izmenili klijenta.';
  public const CLIENT_SUSPENDED_TRUE = 'Uspešno ste deaktivirali klijenta.';
  public const CLIENT_SUSPENDED_FALSE = 'Uspešno ste aktivirali klijenta.';


  //Poruke za komentare
  public const COMMENT_ADD = 'Uspešno ste kreirali novi komentar.';
  public const COMMENT_DELETE = 'Uspešno ste obrisali komentar.';
  public const COMMENT_EDIT = 'Uspešno ste izmenili komentar.';

  //Poruke za kalendar
  public const CALENDAR_ADD = 'Uspešno ste kreirali novi zahtev.';
  public const CALENDAR_DELETE = 'Uspešno ste obrisali zahtev.';
  public const CALENDAR_EDIT = 'Uspešno ste izmenili zahtev.';

    public const CALENDAR_ERROR = 'Zahtev je već kreiran. Niste kreirali zahtev.';

  //Poruke za beleske
  public const NOTE_ADD = 'Uspešno ste kreirali novu belešku.';
  public const NOTE_DELETE = 'Uspešno ste obrisali belešku.';
  public const NOTE_EDIT = 'Uspešno ste izmenili belešku.';

  //Poruke za vremenske zapise
  public const STOPWATCH_ADD = 'Uspešno ste kreirali novo merenje.';
  public const STOPWATCH_ADD_ERROR = 'Niste kreirali novo merenje. Proverite vreme koje unosite jer je moguće da se preklapaju sa već kreiranim merenjima.';
  public const STOPWATCH_START_ERROR = 'Nije moguće pokrenuti novo merenje. Merenje je već pokrenuto.';
  public const STOPWATCH_DELETE = 'Uspešno ste obrisali merenje.';
  public const STOPWATCH_EDIT = 'Uspešno ste izmenili merenje.';
  public const STOPWATCH_CLOSE = 'Uspešno ste zatvorili merenje.';

  public const STOPWATCH_CHECKED = 'Uspešno ste potvrdili merenje i više se neće pojavljivati u listi.';

  public const TIME_TASK_CLOSE = 'Uspešno ste zatvorili merenje.';

  //Poruke za zadatke
  public const TASK_ADD = 'Uspešno ste kreirali/izmenili zadatak.';
  public const TASK_MERGE = 'Uspešno ste spojili zadatke.';
  public const TASK_ADD_ERROR = 'Zadatak nije sačuvan jer ste ga verovatno već kreirali.';
  public const TASK_DELETE = 'Uspešno ste obrisali zadatak.';
  public const TASK_EDIT = 'Uspešno ste izmenili zadatak.';
  public const TASK_CLOSE = 'Uspešno ste zatvorili zadatak.';
  public const TASK_REASSIGN = 'Uspešno ste izmenili zadužene na zadatku.';
  public const TASK_REASSIGN_ADD = 'Uspešno ste dodali novog zaduženog na zadatku.';
  public const TASK_REASSIGN_REMOVE = 'Uspešno ste uklonili zaduženog sa zadatka.';
  public const TASK_REASSIGN_REMOVE_ERROR = 'Nije moguće ukloniti zaposlenog jer ima meranje ili je vozilo za zadatak njemu dodeljeno.';
  public const TASK_LOG_PRIMARY = 'Uspešno ste izmenili primarni dnevnik.';

  //Poruke za vozila
  public const CAR_ADD = 'Uspešno ste kreirali/izmenili vozilo.';
  public const CAR_ADD_IMAGE = 'Uspešno ste dodali sliku za vozilo.';
  public const CAR_RESERVE = 'Uspešno ste kreirali/izmenili rezervaciju za vozilo.';
  public const CAR_EXPENSE = 'Uspešno ste kreiali/izmenili trošak za vozilo.';
  public const CAR_DELETE_EXPENSE = 'Uspešno ste obrisali trošak za vozilo.';
  public const CAR_DEACTIVATE = 'Uspešno ste dekativirali vozilo.';
  public const CAR_ACTIVATE = 'Uspešno ste aktivirali vozilo.';

  //Poruke za opremu
  public const TOOL_ADD = 'Uspešno ste kreirali/izmenili opremu.';
  public const TOOL_RESERVE = 'Uspešno ste kreirali/izmenili rezervaciju za opremu.';
  public const TOOL_DEACTIVATE = 'Uspešno ste dekativirali opremu.';
  public const TOOL_ACTIVATE = 'Uspešno ste aktivirali opremu.';

  public const TOOL_TYPE_ADD = 'Uspešno ste kreirali/izmenili tip opreme.';
  public const TOOL_TYPE_DEACTIVATE = 'Uspešno ste dekativirali tip opreme.';
  public const TOOL_TYPE_ACTIVATE = 'Uspešno ste aktivirali tip opreme.';

  //Poruke za planove
  public const PLAN_ERROR = 'Nije moguće kreirati plan za ovaj datum. Verovatno je već kreiran.';
  public const PLAN_ERROR_DELETE = 'Nije moguće obrisati plan za ovaj datum. Zadaci su već kreirani.';
  public const PLAN_ADD = 'Uspešno ste kreirali/izmenili plan.';
  public const PLAN_DELETE = 'Uspešno ste obrisali plan.';



  public const DOC_ADD = 'Uspešno ste dodali dokument.';
  public const DOC_DELETE = 'Uspešno ste obrisali dokument.';

  public const DOC_ADD_ERROR = 'Niste dodali dokument. Premašuje veličinu od 2Mb.';

  public const PIC_ADD = 'Uspešno ste dodali sliku.';
  public const PIC_DELETE = 'Uspešno ste obrisali sliku.';



  public const DELETE_ERROR = 'Nije moguće obrisati zadatak jer je kreiran kroz plan. Izmenite plan.';
  public const EDIT_ERROR = 'Nije moguće izmeniti zadatak jer je kreiran kroz plan. Izmenite plan.';


  //Poruke za interne zadatke
  public const CHECKLIST_ADD = 'Uspešno ste kreirali interni zadatak.';
  public const CHECKLIST_CONVERT = 'Uspešno ste konvertovali interni zadatak u zadatak sa merenjem vremena.';
  public const CHECKLIST_DELETE = 'Uspešno ste obrisali interni zadatak.';
  public const CHECKLIST_EDIT = 'Uspešno ste izmenili interni zadatak.';
  public const CHECKLIST_CLOSE = 'Uspešno ste završili interni zadatak.';
  public const CHECKLIST_START = 'Uspešno ste započeli interni zadatak.';
  public const CHECKLIST_REPLAY = 'Uspešno ste vratili status internog zadatka na početni.';
  public const CHECKLIST_EDIT_ERROR = 'Niste izmenili interni zadatak jer je zadatak u toku.';
  public const CHECKLIST_CONVERT_ERROR = 'Niste konvertovali interni zadatak jer je zadatak u toku.';

  public const TICKET_ADD = 'Uspešno ste kreirali/izmenili tiket.';
  public const TICKET_CONVERT = 'Uspešno ste konvertovali tiket u zadatak.';
  public const TICKET_FINISH = 'Uspešno ste rešili tiket.';
  public const TICKET_DELETE = 'Uspešno ste obrisali tiket.';

  public const ELABORAT_ADD = 'Uspešno ste kreirali/izmenili elaborat.';
  public const ELABORAT_DELETE = 'Uspešno ste obrisali elaborat.';


  public const VERIFY_ACTIVITY_CHECKED = 'Prihvatili aktivnost.';
  public const VERIFY_ACTIVITY_UNCHECKED = 'Odbili ste aktivnost.';
  public const VERIFY_ACTIVITY_DELETE = 'Obrisali ste aktivnost.';

  public const VERIFY_SURVEY_ADD = 'Uspešno je zabeležen Vaš glas.';
  public const VERIFY_SURVEY_ERROR = 'Došlo je do greške prilikom glasanja. Moguće je da ste već glasali.';


  public const SIGNATURE_ERROR = 'Nije moguće dodati novi zahtev. Već je kreiran za traženo gradilište.';

}
