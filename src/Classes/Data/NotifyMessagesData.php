<?php

namespace App\Classes\Data;
class NotifyMessagesData {

  public const REGISTRATION_USER_SUCCESS = 'Uspešno ste kreirali novog korisnika.';
  public const EDIT_USER_SUCCESS = 'Uspešno ste izmenili korisnički profil.';
  public const EDIT_USER_IMAGE_SUCCESS = 'Uspešno ste izmenili profilnu sliku korisnika.';
  public const USER_SUSPENDED_TRUE = 'Uspešno ste deaktivirali nalog.';
  public const USER_SUSPENDED_FALSE = 'Uspešno ste aktivirali nalog.';
  public const USER_SUSPENDED_QUESTION = 'Da li zaista želite da deaktivirate nalog?';



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

  //Poruke za beleske
  public const NOTE_ADD = 'Uspešno ste kreirali novu belešku.';
  public const NOTE_DELETE = 'Uspešno ste obrisali belešku.';
  public const NOTE_EDIT = 'Uspešno ste izmenili belešku.';

  //Poruke za vremenske zapise
  public const STOPWATCH_ADD = 'Uspešno ste kreirali novo merenje.';
  public const STOPWATCH_ADD_ERROR = 'Niste kreirali novo merenje. Proverite vreme koje unosite jer je moguće da se preklapaju sa već kreiranim merenjima. ';
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
  public const TASK_REASSIGN_REMOVE_ERROR = 'Nije moguće ukloniti zaposlenog jer ima meranje.';
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

  //Poruke za planove
  public const PLAN_ERROR = 'Nije moguće kreirati plan za ovaj datum. Verovatno je već kreiran.';
  public const PLAN_ADD = 'Uspešno ste kreirali/izmenili plan.';
  public const PLAN_DELETE = 'Uspešno ste obrisali plan.';



  public const DOC_ADD = 'Uspešno ste dodali dokument.';
  public const DOC_DELETE = 'Uspešno ste obrisali dokument.';

  public const PIC_ADD = 'Uspešno ste dodali sliku.';
  public const PIC_DELETE = 'Uspešno ste obrisali sliku.';


  public const DELETE_ERROR = 'Nije moguće obrisati zadatak jer je kreiran kroz plan. Izmenite plan.';
  public const EDIT_ERROR = 'Nije moguće izmeniti zadatak jer je kreiran kroz plan. Izmenite plan.';


}
