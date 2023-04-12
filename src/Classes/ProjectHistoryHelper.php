<?php

namespace App\Classes;


class ProjectHistoryHelper {
  private $id;
  private $title;
  private $description;
  private $isSuspended;
  private $isTimeRoundUp;
  private $isEstimate;
  private $isClientView;
  private $category;
  private $client;
  private $label;
  private $editBy;
  private $payment;
  private $price;
  private $pricePerHour;
  private $pricePerTask;
  private $currency;
  private $minEntry;
  private $roundingInterval;
  private $deadline;

  public function __construct($id, $title, $description, $isSuspended, $isTimeRoundUp, $isEstimate, $isClientView, $category, $client, $label, $editBy, $payment, $price, $pricePerHour, $pricePerTask, $currency, $minEntry, $roundingInterval, $deadline) {
    $this->id = $id;
    $this->title = $title;
    $this->description = $description;
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
    $this->currency = $currency;
    $this->minEntry = $minEntry;
    $this->roundingInterval = $roundingInterval;
    $this->deadline = $deadline;
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



}