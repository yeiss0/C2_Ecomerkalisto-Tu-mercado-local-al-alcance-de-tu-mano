import tkinter as tk

class CoorApp:
    def __init__(self, master):
        self.master = master
        master.title("CoorApp")
        master.geometry("400x450")
        master.configure(bg="white")  # Fondo en blanco

        # Título en verde claro
        frm_title = tk.Frame(master, bg="#D5FF6A", relief="solid", bd=0)  # Color verde claro
        frm_title.pack(fill="x")
        lbl_title = tk.Label(frm_title, text="CoorApp", font=("Arial", 24, "bold"), bg="#D5FF6A", fg="black")
        lbl_title.pack(pady=10)

        # Frame principal
        frm = tk.Frame(master, bg="white")
        frm.pack(padx=20, pady=20, expand=True, fill="both")

        # Función simple para crear un campo de entrada
        def crear_elemento(lbl_texto, fila, columna, span=1, center=False):
            lbl = tk.Label(frm, text=lbl_texto, bg="#FFF891", fg="black", font=("Arial", 12))  # Color amarillo claro
            lbl.grid(row=fila, column=columna, columnspan=span, sticky="ew" if center else "w", padx=10, pady=5)
            ent = tk.Entry(frm, bg="#C4C4C4", relief="flat")  # Color gris claro para entrada
            ent.grid(row=fila+1, column=columna, columnspan=span, sticky="ew", padx=10)

        # Campos
        crear_elemento("Nombre", 0, 0)
        crear_elemento("Apellido", 0, 1)
        crear_elemento("Correo Electrónico", 2, 0, span=2, center=True)
        crear_elemento("Crear Contraseña", 4, 0)
        crear_elemento("Confirmar Contraseña", 4, 1)

        # Botón
        btn_siguiente = tk.Button(frm, text="Siguiente", bg="#FFF891", fg="black", font=("Arial", 12), relief="flat")  # Color amarillo claro
        btn_siguiente.grid(row=6, column=1, sticky="e", padx=10, pady=20)

        # Distribuir espacio equitativamente
        for i in range(2):
            frm.columnconfigure(i, weight=1)
        for i in range(7):
            frm.rowconfigure(i, weight=1)

# Crear ventana principal
root = tk.Tk()
app = CoorApp(root)
root.mainloop()

