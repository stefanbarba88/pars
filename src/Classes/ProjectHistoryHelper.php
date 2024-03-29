<?php

namespace App\Classes;


class ProjectHistoryHelper {
  private $id;
  private $title;
  private $description;
  private $important;
  private $isSuspended;
  private $isTimeRoundUp;
  private $isEstimate;
  private $isClientView;
  private $isViewLog;
  private $category;
  private $client;
  private $timerPriority;
  private $label;
  private $editBy;
  private $payment;
  private $type;
  private $price;
  private $pricePerHour;
  private $pricePerTask;
  private $pricePerDay;
  private $pricePerMonth;
  private $currency;
  private $minEntry;
  private $roundingInterval;
  private $deadline;
  private $team;

  public function __construct($id, $title, $description, $important, $type, $isSuspended, $isTimeRoundUp, $isEstimate, $isClientView, $isViewLog, $team, $category, $client, $timerPriority, $label, $editBy, $payment, $price, $pricePerHour, $pricePerDay, $pricePerMonth, $pricePerTask, $currency, $minEntry, $roundingInterval, $deadline) {
    $this->id = $id;
    $this->title = $title;
    $this->description = $description;
    $this->important = $important;
    $this->isSuspended = $isSuspended;
    $this->isTimeRoundUp = $isTimeRoundUp;
    $this->isEstimate = $isEstimate;
    $this->isClientView = $isClientView;
    $this->category = $category;
    $this->client = $client;
    $this->label = $label;
    $this->editBy = $editBy;
    $this->payment = $payment;
    $this->price = $price;
    $this->pricePerHour = $pricePerHour;
    $this->pricePerTask = $pricePerTask;
    $this->pricePerDay = $pricePerDay;
    $this->pricePerMonth = $pricePerMonth;
    $this->currency = $currency;
    $this->minEntry = $minEntry;
    $this->roundingInterval = $roundingInterval;
    $this->deadline = $deadline;
    $this->timerPriority = $timerPriority;
    $this->isViewLog = $isViewLog;
    $this->team = $team;
    $this->type = $type;
  }

  /**
   * @return mixed
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @param mixed $id
   */
  public function setId($id): void {
    $this->id = $id;
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param mixed $title
   */
  public function setTitle($title): void {
    $this->title = $title;
  }

  /**
   * @return mixed
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param mixed $description
   */
  public function setDescription($description): void {
    $this->description = $description;
  }

  /**
   * @return mixed
   */
  public function getIsSuspended() {
    return $this->isSuspended;
  }

  /**
   * @param mixed $isSuspended
   */
  public function setIsSuspended($isSuspended): void {
    $this->isSuspended = $isSuspended;
  }

  /**
   * @return mixed
   */
  public function getIsTimeRoundUp() {
    return $this->isTimeRoundUp;
  }

  /**
   * @param mixed $isTimeRoundUp
   */
  public function setIsTimeRoundUp($isTimeRoundUp): void {
    $this->isTimeRoundUp = $isTimeRoundUp;
  }

  /**
   * @return mixed
   */
  public function getIsEstimate() {
    return $this->isEstimate;
  }

  /**
   * @param mixed $isEstimate
   */
  public function setIsEstimate($isEstimate): void {
    $this->isEstimate = $isEstimate;
  }

  /**
   * @return mixed
   */
  public function getIsClientView() {
    return $this->isClientView;
  }

  /**
   * @param mixed $isClientView
   */
  public function setIsClientView($isClientView): void {
    $this->isClientView = $isClientView;
  }

  /**
   * @return mixed
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * @param mixed $category
   */
  public function setCategory($category): void {
    $this->category = $category;
  }

  /**
   * @return mixed
   */
  public function getClient() {
    return $this->client;
  }

  /**
   * @param mixed $client
   */
  public function setClient($client): void {
    $this->client = $client;
  }

  /**
   * @return mixed
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @param mixed $label
   */
  public function setLabel($label): void {
    $this->label = $label;
  }

  /**
   * @return mixed
   */
  public function getEditBy() {
    return $this->editBy;
  }

  /**
   * @param mixed $editBy
   */
  public function setEditBy($editBy): void {
    $this->editBy = $editBy;
  }

  /**
   * @return mixed
   */
  public function getPayment() {
    return $this->payment;
  }

  /**
   * @param mixed $payment
   */
  public function setPayment($payment): void {
    $this->payment = $payment;
  }

  /**
   * @return mixed
   */
  public function getPrice() {
    return $this->price;
  }

  /**
   * @param mixed $price
   */
  public function setPrice($price): void {
    $this->price = $price;
  }

  /**
   * @return mixed
   */
  public function getPricePerHour() {
    return $this->pricePerHour;
  }

  /**
   * @param mixed $pricePerHour
   */
  public function setPricePerHour($pricePerHour): void {
    $this->pricePerHour = $pricePerHour;
  }

  /**
   * @return mixed
   */
  public function getPricePerTask() {
    return $this->pricePerTask;
  }

  /**
   * @param mixed $pricePerTask
   */
  public function setPricePerTask($pricePerTask): void {
    $this->pricePerTask = $pricePerTask;
  }

  /**
   * @return mixed
   */
  public function getCurrency() {
    return $this->currency;
  }

  /**
   * @param mixed $currency
   */
  public function setCurrency($currency): void {
    $this->currency = $currency;
  }

  /**
   * @return mixed
   */
  public function getMinEntry() {
    return $this->minEntry;
  }

  /**
   * @param mixed $minEntry
   */
  public function setMinEntry($minEntry): void {
    $this->minEntry = $minEntry;
  }

  /**
   * @return mixed
   */
  public function getRoundingInterval() {
    return $this->roundingInterval;
  }

  /**
   * @param mixed $roundingInterval
   */
  public function setRoundingInterval($roundingInterval): void {
    $this->roundingInterval = $roundingInterval;
  }

  /**
   * @return mixed
   */
  public function getDeadline() {
    return $this->deadline;
  }

  /**
   * @param mixed $deadline
   */
  public function setDeadline($deadline): void {
    $this->deadline = $deadline;
  }

  /**
   * @return mixed
   */
  public function getTimerPriority() {
    return $this->timerPriority;
  }

  /**
   * @param mixed $timerPriority
   */
  public function setTimerPriority($timerPriority): void {
    $this->timerPriority = $timerPriority;
  }

  /**
   * @return mixed
   */
  public function getIsViewLog() {
    return $this->isViewLog;
  }

  /**
   * @param mixed $isViewLog
   */
  public function setIsViewLog($isViewLog): void {
    $this->isViewLog = $isViewLog;
  }

  /**
   * @return mixed
   */
  public function getTeam() {
    return $this->team;
  }

  /**
   * @param mixed $team
   */
  public function setTeam($team): void {
    $this->team = $team;
  }

  /**
   * @return mixed
   */
  public function getPricePerDay() {
    return $this->pricePerDay;
  }

  /**
   * @param mixed $pricePerDay
   */
  public function setPricePerDay($pricePerDay): void {
    $this->pricePerDay = $pricePerDay;
  }

  /**
   * @return mixed
   */
  public function getPricePerMonth() {
    return $this->pricePerMonth;
  }

  /**
   * @param mixed $pricePerMonth
   */
  public function setPricePerMonth($pricePerMonth): void {
    $this->pricePerMonth = $pricePerMonth;
  }

  /**
   * @return mixed
   */
  public function getImportant() {
    return $this->important;
  }

  /**
   * @param mixed $important
   */
  public function setImportant($important): void {
    $this->important = $important;
  }

  /**
   * @return mixed
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param mixed $type
   */
  public function setType($type): void {
    $this->type = $type;
  }





}