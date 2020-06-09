<?php
namespace App\Libraries;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Models\UserModel;
use App\Models\ConnectionsModel;
class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $uriQuery=$conn->httpRequest->getUri()->getQuery();
        $uriQueryArr=explode('=',$uriQuery);
        $userModel=new UserModel();
        $connectionModel=new ConnectionsModel();
        $user=$userModel->find($uriQueryArr[1]);
        $conn->user=$user;
        $this->clients->attach($conn);
        $connectionModel->where('userid',$user['id'])->delete();
        $conData=[
            'resourceid'=>$conn->resourceId,
            'userid'=>$user['id'],
            'name'=>$user['firstname']
        ];
        $connectionModel->save($conData);
        $users=$connectionModel->findAll();
        $users=['users'=>$users];
        foreach ($this->clients as $client) {
           $client->send(json_encode($users));
        }
      

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $data=[
                    'message'=>$msg,
                    'author'=>$from->user['firstname'],
                    'time'=>date("H:i")
                ];
                $client->send(json_encode($data));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $connectionModel=new ConnectionsModel();
        $connectionModel->where('resourceid',$conn->resourceId)->delete();
        $users=$connectionModel->findAll();
        $users=['users'=>$users];
        foreach($this->clients as $client)
        {
            $client->send(json_encode($users));
        }
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}