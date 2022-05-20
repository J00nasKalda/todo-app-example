<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class TodoCreateCommand
 *
 * @package App\Command
 */
class TodoCreateCommand extends Command
{

  protected static $defaultName = 'app:add-todo-item';

  /**
   * @var mixed|string|null
   */
  private mixed $todoName;

  /**
   * @var mixed|string|null
   */
  private mixed $todoDescription;

  /**
   * @var mixed|string|null
   */
  private mixed $todoDueDate;

  /**
   * @var mixed|string|null
   */
  private mixed $todoConfirm;

  public function __construct()
  {
    parent::__construct();
  }


  protected function configure()
  {
    $this->addArgument('input', InputArgument::OPTIONAL, 'The input of the client.');
  }

  /**
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   *
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    return Command::SUCCESS;
  }

  protected function interact(InputInterface $input, OutputInterface $output)
  {
    $token = $this->getToken();

    if (!empty($token)) {

      $this->setTodoData($input, $output);

      $this->addTodoItem($token);

      $output->writeln("Successfully created new Todo item");

      return Command::SUCCESS;
    }

    return Command::FAILURE;
  }

  protected function setTodoData($input, $output) {
    $helper = $this->getHelper('question');
    $question1 = new Question('Name of the Todo item: ', 'default name');
    $question2 = new Question('Description: ', '');
    $question3 = new Question('Due date (Y-m-d H:i:s): ', date('Y-m-d H:i:s'));
    $confirm = new ConfirmationQuestion('Continue with this action? (yes):', false);

    $this->todoName = $helper->ask($input, $output, $question1);
    $this->todoDescription = $helper->ask($input, $output, $question2);
    $this->todoDueDate = $helper->ask($input, $output, $question3);
    $this->todoConfirm = $helper->ask($input, $output, $confirm);
  }

  protected function getToken() {
    return $this->makeApiRequest('GET', '/session/token', $this->getDefaultHeaders());
  }

  protected function addTodoItem($token) {
    $headers = $this->getDefaultHeaders();
    $headers['X-CSRF-Token'] = $token;

    $json = [
      'name' => $this->todoName,
      'description' => $this->todoDescription,
      'due_date' => $this->todoDueDate
    ];

    $auth = [$_ENV['DRUPAL_USER'], $_ENV['DRUPAL_PASS']];

    $this->makeApiRequest('PUT', '/api/todo-list/add', $headers, $json, $auth);
  }

  protected function makeApiRequest($type = 'GET', $path = '', $headers = [], $json = [], $auth = []) {
    $url = $_ENV['DRUPAL_HOST'] . $path;

    $client = new Client();
    $response = $client->request($type, $url, ['headers' => $headers, 'json' => $json, 'auth' => $auth]);

    return $response->getBody()->getContents();
  }

  protected function getDefaultHeaders() {
    return [
      'Content-type' => 'application/json',
    ];
  }
}
