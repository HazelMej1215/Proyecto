package com.example.streamingapp.ui

import android.content.Intent
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.RecyclerView
import com.bumptech.glide.Glide
import com.example.streamingapp.R
import com.example.streamingapp.TrailerActivity
import com.example.streamingapp.network.Pelicula

class PeliculasAdapter(
    private val baseWeb: String,
    private val data: MutableList<Pelicula>
) : RecyclerView.Adapter<PeliculasAdapter.VH>() {

    class VH(v: View) : RecyclerView.ViewHolder(v) {
        val img: ImageView = v.findViewById(R.id.imgPoster)
        val nombre: TextView = v.findViewById(R.id.tvNombre)
        val genero: TextView = v.findViewById(R.id.tvGenero)
        val desc: TextView = v.findViewById(R.id.tvDesc)
        val btn: Button = v.findViewById(R.id.btnVer)
    }

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VH {
        val v = LayoutInflater.from(parent.context).inflate(R.layout.item_pelicula, parent, false)
        return VH(v)
    }

    override fun getItemCount() = data.size

    override fun onBindViewHolder(h: VH, position: Int) {
        val p = data[position]
        h.nombre.text = p.nombre
        h.genero.text = p.genero
        h.desc.text = p.descripcion

        // ruta_imagen viene como: uploads/...
        val urlImg = baseWeb + p.ruta_imagen
        Glide.with(h.itemView).load(urlImg).into(h.img)

        h.btn.setOnClickListener {
            val ctx = h.itemView.context
            val i = Intent(ctx, TrailerActivity::class.java)
            i.putExtra("url", p.url_trailer)
            ctx.startActivity(i)
        }
    }

    fun setItems(items: List<Pelicula>) {
        data.clear()
        data.addAll(items)
        notifyDataSetChanged()
    }
}