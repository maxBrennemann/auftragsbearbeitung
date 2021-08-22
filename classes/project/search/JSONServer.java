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

            System.out.println(path);

            PrintWriter out = new PrintWriter(sock.getOutputStream(), true);
            String s = "<html><head><title>test</title></head><body><h1>test</h1></body></html>";

	        out.println("HTTP/1.1 200 OK");
	        out.println("Content-Type: text/html");
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