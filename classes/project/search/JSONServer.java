import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.ServerSocket;
import java.net.Socket;

public class JSONServer {

    
    public static void main(String[] args) throws IOException {
        while (true) {
            ServerSocket serverSock = new ServerSocket(8081);
            Socket sock = serverSock.accept();

            System.out.println("connected");

            InputStream sis = sock.getInputStream();
            BufferedReader br = new BufferedReader(new InputStreamReader(sis));
            String request = br.readLine();
            String[] requestParam = request.split(" ");
            String path = requestParam[1];
            
            String searchQuery = path;

            System.out.println(path);
            
            Search search = new Search();
            String[] result = search.initializeSearch(path);
            result[0] = result[0].replace("\n", "").replace("\r", "");
            System.out.println(result[0]);

            PrintWriter out = new PrintWriter(sock.getOutputStream(), true);
            String s = "{0: \"" + result[0] + "\"}";

	        out.println("HTTP/1.1 200 OK");
	        out.println("Content-Type: application/json");
	        out.println("Content-Length: " + s.length());
	        out.println("\r\n");
	        out.write(s);
	        out.flush();
	            
            br.close();
            out.close();
            serverSock.close();
        }
    }


}