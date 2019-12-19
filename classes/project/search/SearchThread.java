import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.OutputStreamWriter;
import java.io.PrintWriter;
import java.net.Socket;

public class SearchThread extends Thread {
    
    private Socket client;
    
    public SearchThread(Socket client) {
        this.client = client;
    }
    
    @Override
    public void run() {
        try {
            String command = "";
            String[] params;
            BufferedReader reader = new BufferedReader(new InputStreamReader(client.getInputStream()));
            PrintWriter out = new PrintWriter(client.getOutputStream(), true);
                    //new PrintWriter(new OutputStreamWriter(client.getOutputStream()));

            mainloop: while (true) {
                command = reader.readLine();
                
                if (command == null) {
                    break mainloop;
                }
                
                params = command.split("\\s+", 3);
                
                if (command.equals("stopProgram")) {
                    break mainloop;
                }
                
                if (params.length != 3) {
                    for (int i = 0; i < params.length; i++) {
                        out.println(params[i]);
                    }
                    continue mainloop;
                }
                
                DocumentType type = DocumentType.valueOf(params[1]);
                DocumentCollection current;
                
                switch (type) {
                    case Customer:
                        current = Search.documentCollectionCustomers;
                        break;
                    case Order:
                        current = Search.documentCollectionOrders;
                        break;
                    case Product:
                        current = Search.documentCollectionProducts;
                        break;
                    default:
                        continue mainloop;
                }
                
                if (params[0].equals("add")) {
                    Document document;
                    String data = params[2];
                    document = new Document(data, type);
                    
                    current.appendDocument(document);
                    
                    System.out.println("added " + type + " with data: " + data);
                } else if (params[0].equals("search")) {
                    String query = params[2];
                    
                    current.match(query);
                    
                    System.out.println("searching...");
                    
                    int length = current.numDocuments();
                    for (int i = 0; i < length; i++) {
                        out.println(current.get(i).getContent());
                    }
                    
                    out.println("resultEnd");
                    out.flush();
                    System.out.println("searched for " + type + " with query: " + query);
                }
            }
            
            out.flush();
        } catch (IOException e) {
            // nothing to do here
        } finally {
            try {
                client.close();
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }

}
