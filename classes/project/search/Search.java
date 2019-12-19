import java.io.IOException;
import java.net.ServerSocket;
import java.net.Socket;

public class Search {

    public static DocumentCollection documentCollectionCustomers;
    public static DocumentCollection documentCollectionOrders;
    public static DocumentCollection documentCollectionProducts;

    /*
    * syntax:
    * add [type] [id] [data] -> returns currently nothing
    * search [type] [query] -> returns id list of potential matches
    */

    public static void main(String[] args) throws IOException {
        documentCollectionCustomers = new DocumentCollection();
        documentCollectionOrders = new DocumentCollection();
        documentCollectionProducts = new DocumentCollection();
        
        ServerSocket serverSocket = new ServerSocket(29180);
        while(true) {
            Socket client = serverSocket.accept();
            SearchThread searchThread = new SearchThread(client);
            searchThread.start();
        }
    }
    
}
