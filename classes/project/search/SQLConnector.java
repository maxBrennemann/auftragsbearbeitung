
import java.sql.*;
import java.util.ArrayList;

public class SQLConnector {

	@SuppressWarnings("deprecation")
	public static void main(String args[]) {
		try {
            // The newInstance() call is a work around for some
            // broken Java implementations

            Class.forName("com.mysql.jdbc.Driver").newInstance();
            
            Connection con = DriverManager.getConnection("jdbc:mysql://localhost:3306/auftragsmanager", "root", "");
            
            Statement statement = con.createStatement();
            ResultSet rs = statement.executeQuery("SELECT * FROM kunde");
            
            while (rs.next()) {
            	System.out.println(rs.getInt(1));
            }
        } catch (Exception ex) {
            // handle the error
        	
        	System.out.println("SQLException: " + ex.getMessage());
        }
	}
	
	
	@SuppressWarnings("deprecation")
	private ResultSet executeStatement(String mySQLStatement) {
		try {
            // The newInstance() call is a work around for some
            // broken Java implementations

            Class.forName("com.mysql.jdbc.Driver").newInstance();
            
            Connection con = DriverManager.getConnection("jdbc:mysql://localhost:3306/auftragsmanager", "root", "");
            
            Statement statement = con.createStatement();
            ResultSet rs = statement.executeQuery("SELECT * FROM Kunde");

            return rs;
        } catch (Exception ex) {
            // handle the error
        }
		
		return null;
	}
	
	public ArrayList<String> getCustomerData() {
		String searchQuery = "SELECT kunde.Kundennummer, Firmenname, kunde.Vorname, kunde.Nachname, ansprechpartner.Vorname, ansprechpartner.Nachname FROM kunde, ansprechpartner WHERE kunde.Kundennummer = ansprechpartner.Kundennummer ORDER BY kunde.Kundennummer";
		ResultSet rs = executeStatement(searchQuery);
		
		ArrayList<String> results = new ArrayList();
		try {
			while (rs.next()) {
				results.add(rs.getString(2) + " " + rs.getString(3) + " " + rs.getString(4) + " " + rs.getString(5) + " " + rs.getString(6));
			}
		} catch (Exception e) {
			
		}
		
		return results;
	}
	
}
